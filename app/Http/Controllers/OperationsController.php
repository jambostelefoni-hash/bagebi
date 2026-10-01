<?php
namespace App\Http\Controllers;
use App\Model\NotificationDelivery;
use App\Model\PlacementOffer;
use App\Model\WaitingListEntry;
use App\Model\Kindergarten;
use App\Model\GroupAgeRange;
use App\Model\ShortActionLink;
use App\Notifications\ParentSmsNotification;
use App\Services\SmsOfficeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
class OperationsController extends Controller
{
    public function index(Request $request)
    {
        $waiting=WaitingListEntry::with(['kindergartener','kindergarten','groupRange'])->whereIn('state',['waiting','offered'])
            ->when($request->waiting_kindergarten_id,fn($q,$id)=>$q->where('kindergarten_id',$id))->when($request->waiting_group_id,fn($q,$id)=>$q->where('group_id',$id))->when($request->waiting_state,fn($q,$state)=>$q->where('state',$state))->orderBy('priority_rank')->orderBy('queued_at')->paginate(20,['*'],'waiting_page');
        $offers=PlacementOffer::with('entry.kindergartener')->whereHas('entry',function($q)use($request){$q->when($request->offer_kindergarten_id,fn($q,$id)=>$q->where('kindergarten_id',$id))->when($request->offer_group_id,fn($q,$id)=>$q->where('group_id',$id));})
            ->when($request->offer_response==='pending',fn($q)=>$q->whereNull('response'))->when(in_array($request->offer_response,['accepted','declined','expired'],true),fn($q)=>$q->where('response',$request->offer_response))->latest()->paginate(20,['*'],'offers_page');
        $notificationBase = NotificationDelivery::query();
        $notificationSummary = [
            'queued' => (clone $notificationBase)->whereIn('status', ['queued', 'sending'])->count(),
            'sent' => (clone $notificationBase)->where('status', 'sent')->where(function ($query) {
                $query->whereNull('provider_status')->orWhere('provider_status', 'Pending');
            })->count(),
            'delivered' => (clone $notificationBase)->where('provider_status', 'Delivered')->count(),
            'failed' => (clone $notificationBase)->where(function ($query) {
                $query->where('status', 'failed')->orWhereIn('provider_status', ['Undelivered', 'Expired']);
            })->count(),
            'attention' => (clone $notificationBase)->where(function ($issues) {
                $issues->where('status', 'failed')
                    ->orWhereIn('provider_status', ['Undelivered', 'Expired'])
                    ->orWhere(function ($stalled) {
                        $stalled->whereIn('status', ['queued', 'sending'])->where('created_at', '<', now()->subMinutes(15));
                    });
            })->count(),
        ];
        $notifications = NotificationDelivery::with('kindergartener')
            ->when($request->notification_view === 'attention', function ($query) {
                $query->where(function ($issues) {
                    $issues->where('status', 'failed')
                        ->orWhereIn('provider_status', ['Undelivered', 'Expired'])
                        ->orWhere(function ($stalled) {
                            $stalled->whereIn('status', ['queued', 'sending'])->where('created_at', '<', now()->subMinutes(15));
                        });
                });
            })
            ->when($request->notification_search, function ($query, $search) {
                $query->whereHas('kindergartener', function ($child) use ($search) {
                    $child->where(function ($fields) use ($search) {
                        $fields->where('kids_first_name', 'like', '%'.$search.'%')
                            ->orWhere('kids_last_name', 'like', '%'.$search.'%')
                            ->orWhere('mobile_number', 'like', '%'.$search.'%');
                    });
                });
            })
            ->when($request->notification_status, fn($q,$status)=>$q->where('status',$status))
            ->when($request->delivery_status,fn($q,$status)=>$q->where('provider_status',$status))
            ->when($request->notification_event,fn($q,$event)=>$q->where('event',$event))
            ->latest()->paginate(20,['*'],'notifications_page');
        $capacity=DB::table('kindergarten_group_age_range')->selectRaw('SUM(CAST(space_length AS UNSIGNED)) total, SUM(CAST(space_filled AS UNSIGNED)) filled, SUM(CAST(space_reserved AS UNSIGNED)) reserved, SUM(CAST(space_free AS UNSIGNED)) free')->first();
        $gardens=Kindergarten::orderBy('name')->pluck('name','id'); $groups=GroupAgeRange::orderBy('range')->pluck('range','id');
        return view('operations.index',compact('waiting','offers','notifications','notificationSummary','capacity','gardens','groups'));
    }
    public function processWaitingList()
    {
        Artisan::call('waiting-list:process');
        $this->logAudit('waiting_list.process',WaitingListEntry::class,null,'Waiting list processed manually');
        return back()->with(['flashType'=>'success','flashMessage'=>'რიგი და ვადაგასული შეთავაზებები დამუშავდა.']);
    }
    public function resendNotification($id, SmsOfficeService $smsOffice)
    {
        DB::transaction(function () use ($id, $smsOffice) {
            $delivery = NotificationDelivery::with('kindergartener')->whereKey($id)->lockForUpdate()->firstOrFail();
            $payload = $delivery->payload ?: [];
            abort_unless($delivery->status === 'failed' && $delivery->kindergartener && !empty($payload['message']), 422);
            abort_unless($delivery->channel === 'sms' && config('services.smsoffice.enabled'), 422);
            try {
                $smsOffice->normalizeMobile((string) $delivery->kindergartener->mobile_number);
            } catch (\RuntimeException $error) {
                throw ValidationException::withMessages(['mobile_number' => $error->getMessage()]);
            }
            if (!empty($payload['action_url']) && preg_match('~^/s/([A-Za-z0-9]{20})$~', (string) parse_url($payload['action_url'], PHP_URL_PATH), $match)) {
                $link = ShortActionLink::where('code_hash', hash('sha256', $match[1]))->first();
                if (!$link || $link->expires_at->isPast()) {
                    throw ValidationException::withMessages(['notification' => 'ბმულის ვადა ამოიწურა. ეს შეტყობინება ხელახლა ვერ გაიგზავნება.']);
                }
            }
            $delivery->update(['status' => 'queued', 'last_error' => null]);
            Notification::route('sms', $delivery->kindergartener->mobile_number)
                ->notify((new ParentSmsNotification($payload['message'].(!empty($payload['action_url']) ? "\n".$payload['action_url'] : ''), $delivery->id))->onConnection('database')->beforeCommit());
            $this->logAudit('notification.resend', NotificationDelivery::class, $delivery->id, 'Failed notification queued again',
                ['event' => $delivery->event, 'channel' => $delivery->channel, 'attempts' => $delivery->attempts], true);
        }, 3);
        return back()->with(['flashType'=>'success','flashMessage'=>'SMS ხელახლა გაიგზავნა.']);
    }
}
