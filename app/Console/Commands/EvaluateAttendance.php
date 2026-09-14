<?php
namespace App\Console\Commands;
use App\Model\API\Kindergartener;
use App\Model\Attendance;
use App\Model\ReinstatementRequest;
use App\Services\ApplicationWorkflowService;
use App\Services\ParentNotificationService;
use App\Services\WorkCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
class EvaluateAttendance extends Command
{
    protected $signature='attendance:evaluate {--kindergarten=}'; protected $description='Suspend registrations that exceed absence limits';
    public function handle(ApplicationWorkflowService $workflow,WorkCalendarService $calendar,ParentNotificationService $notifications)
    {
        $kindergartenId=$this->option('kindergarten');
        Kindergartener::where('application_status','enrolled')->when($kindergartenId,fn($query)=>$query->where('kindergarten_id',$kindergartenId))->chunkById(100,function($children)use($workflow,$calendar,$notifications){foreach($children as $child){$monthCount=Attendance::where('kindergartener_id',$child->id)->where('status','absent')->whereBetween('attendance_date',[now()->startOfMonth()->toDateString(),today()->toDateString()])->get()->filter(fn($attendance)=>$calendar->isWorkingDay($attendance->attendance_date,$child->kindergarten_id))->count();$recent=Attendance::where('kindergartener_id',$child->id)->where('attendance_date','<=',today())->orderByDesc('attendance_date')->limit(31)->get()->keyBy(fn($a)=>$a->attendance_date->toDateString());$cursor=today();$streak=0;$first=null;for($i=0;$i<45&&$streak<10;$i++,$cursor->subDay()){if(!$calendar->isWorkingDay($cursor,$child->kindergarten_id))continue;$record=$recent->get($cursor->toDateString());if(!$record||$record->status!=='absent')break;$streak++;$first=$cursor->copy();}if($streak>=10||$monthCount>=15){$workflow->transition($child,'suspended',$streak>=10?'10 consecutive working-day absences':'15 monthly working-day absences');$raw=Str::random(64);ReinstatementRequest::create(['kindergartener_id'=>$child->id,'token_hash'=>hash('sha256',$raw),'expires_at'=>$calendar->addWorkingDays(now(),5,$child->kindergarten_id),'absence_from'=>$first?:now()->startOfMonth(),'absence_to'=>today()]);$notifications->send($child,'registration_suspended','რეგისტრაცია შეჩერებულია','გაცდენების ლიმიტის გამო რეგისტრაცია დროებით შეჩერდა. საპატიო მიზეზის დოკუმენტი ატვირთეთ 5 სამუშაო დღეში.',route('reinstatement.show',$raw));}}});
        ReinstatementRequest::where('status','pending')->where('expires_at','<',now())->with('kindergartener')->each(function($request)use($workflow){$request->update(['status'=>'expired']);if($request->kindergartener->application_status==='suspended')$workflow->transition($request->kindergartener,'cancelled','Reinstatement deadline expired');});
        return 0;
    }
}
