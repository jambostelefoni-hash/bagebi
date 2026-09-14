<?php
namespace App\Console\Commands;
use App\Model\PlacementOffer;
use App\Model\Setting;
use App\Model\WaitingListEntry;
use App\Services\ParentNotificationService;
use App\Services\WorkCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ProcessWaitingList extends Command
{
    protected $signature='waiting-list:process'; protected $description='Expire offers and reserve available places for the waiting list';
    public function handle(WorkCalendarService $calendar,ParentNotificationService $notifications)
    {
        PlacementOffer::whereNull('responded_at')->where('expires_at','<',now())->with('entry')->each(function($offer){DB::transaction(function()use($offer){$entry=$offer->entry;$offer->update(['responded_at'=>now(),'response'=>'expired']);if($entry&&$entry->state==='offered'){DB::table('kindergarten_group_age_range')->where('kindergarten_id',$entry->kindergarten_id)->where('group_age_range',$entry->group_id)->where('space_reserved','>',0)->decrement('space_reserved');$entry->update(['state'=>'waiting']);}});});
        $days=(int)data_get(Setting::where('slug','basic')->first(),'object.offer_working_days',3);
        $capacities=DB::table('kindergarten_group_age_range')->whereRaw('CAST(space_free AS SIGNED) - space_reserved > 0')->get();
        foreach($capacities as $capacity){$available=(int)$capacity->space_free-(int)$capacity->space_reserved;for($i=0;$i<$available;$i++){$raw=Str::random(64);$payload=DB::transaction(function()use($capacity,$raw,$days,$calendar){$current=DB::table('kindergarten_group_age_range')->where('kindergarten_id',$capacity->kindergarten_id)->where('group_age_range',$capacity->group_age_range)->lockForUpdate()->first();if(!$current||(int)$current->space_free-(int)$current->space_reserved<1)return null;$entry=WaitingListEntry::where('kindergarten_id',$capacity->kindergarten_id)->where('group_id',$capacity->group_age_range)->where('state','waiting')->orderBy('priority_rank')->orderBy('queued_at')->lockForUpdate()->first();if(!$entry)return null;DB::table('kindergarten_group_age_range')->where('kindergarten_id',$capacity->kindergarten_id)->where('group_age_range',$capacity->group_age_range)->increment('space_reserved');$entry->update(['state'=>'offered']);$offer=PlacementOffer::create(['waiting_list_entry_id'=>$entry->id,'token_hash'=>hash('sha256',$raw),'expires_at'=>$calendar->addWorkingDays(now(),$days,$entry->kindergarten_id)]);return [$entry->kindergartener,$offer];});if(!$payload)break;[$child,$offer]=$payload;$notifications->send($child,'placement_offer','თავისუფალი ადგილი გამოჩნდა','გთხოვთ, დაადასტუროთ ან უარყოთ შეთავაზება მითითებულ ვადაში.',route('placement-offers.show',$raw));}}
        return 0;
    }
}
