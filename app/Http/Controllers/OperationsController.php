<?php
namespace App\Http\Controllers;
use App\Model\NotificationDelivery;
use App\Model\PlacementOffer;
use App\Model\WaitingListEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
class OperationsController extends Controller
{
    public function index()
    {
        $waiting=WaitingListEntry::with(['kindergartener','kindergarten','groupRange'])->whereIn('state',['waiting','offered'])->orderBy('priority_rank')->orderBy('queued_at')->paginate(20,['*'],'waiting_page');
        $offers=PlacementOffer::with('entry.kindergartener')->latest()->paginate(20,['*'],'offers_page');
        $notifications=NotificationDelivery::with('kindergartener')->latest()->paginate(20,['*'],'notifications_page');
        $capacity=DB::table('kindergarten_group_age_range')->selectRaw('SUM(CAST(space_length AS UNSIGNED)) total, SUM(CAST(space_filled AS UNSIGNED)) filled, SUM(CAST(space_reserved AS UNSIGNED)) reserved, SUM(CAST(space_free AS UNSIGNED)) free')->first();
        return view('operations.index',compact('waiting','offers','notifications','capacity'));
    }
    public function processWaitingList()
    {
        Artisan::call('waiting-list:process');
        $this->logAudit('waiting_list.process',WaitingListEntry::class,null,'Waiting list processed manually');
        return back()->with(['flashType'=>'success','flashMessage'=>'რიგი და ვადაგასული შეთავაზებები დამუშავდა.']);
    }
}
