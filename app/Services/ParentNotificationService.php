<?php
namespace App\Services;
use App\Model\API\Kindergartener;
use App\Model\NotificationDelivery;
use App\Notifications\ParentSmsNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
class ParentNotificationService
{
    public function __construct(private ShortActionLinkService $shortLinks) {}

    public function send(Kindergartener $child, string $event, string $subject, string $message, ?string $actionUrl = null): void
    {
        DB::transaction(function () use ($child, $event, $message, $actionUrl) {
        $baseKey = $event.'|'.$child->id.'|'.($actionUrl ?: $child->status_changed_at);
        if (config('services.smsoffice.enabled') && $child->mobile_number) {
            $key = hash('sha256', $baseKey.'|sms');
            if (NotificationDelivery::where('idempotency_key', $key)->exists()) return;

            $shortActionUrl = $this->shortLinks->create($actionUrl);
            $delivery = NotificationDelivery::firstOrCreate(['idempotency_key'=>$key],['kindergartener_id'=>$child->id,'event'=>$event,'channel'=>'sms','recipient_hash'=>hash('sha256',preg_replace('/\D+/', '', $child->mobile_number)),'payload'=>['message'=>$message,'action_url'=>$shortActionUrl],'status'=>'queued']);
            // Keep the delivery and job in the same database transaction, regardless of the default queue driver.
            if ($delivery->wasRecentlyCreated) Notification::route('sms',$child->mobile_number)->notify((new ParentSmsNotification($this->smsText($message, $shortActionUrl), $delivery->id))->onConnection('database')->beforeCommit());
        }
        });
    }

    private function smsText(string $message, ?string $actionUrl): string
    {
        return $message.($actionUrl ? "\n".$actionUrl : '');
    }
}
