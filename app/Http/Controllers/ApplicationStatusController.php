<?php
namespace App\Http\Controllers;
use App\Model\API\Kindergartener;
use App\Services\ApplicationWorkflowService;
use App\Services\SuspensionWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ApplicationStatusController extends Controller
{
    public function update(Request $request,$id,ApplicationWorkflowService $workflow,SuspensionWorkflowService $suspensions)
    {
        $data=$request->validate(['status'=>['required','in:registered,waiting,enrolled,suspended,cancelled,graduated'],'reason'=>['required','string','min:5','max:500']]);
        return DB::transaction(function () use ($request, $id, $workflow, $suspensions, $data) {
        $child=Kindergartener::whereKey($id)->lockForUpdate()->firstOrFail();$user=$request->user();
        if(!$user->isUnionAdmin()){
            abort_unless((int)$child->kindergarten_id===(int)$user->kindergarten_id,403);
            abort_unless($child->application_status==='enrolled'&&$data['status']==='suspended',403);
        }
        $from = $child->application_status;
        if ($from === $data['status']) return back()->with(['flashType'=>'success','flashMessage'=>'სტატუსი უცვლელია.']);
        if ($data['status'] === ApplicationWorkflowService::SUSPENDED) {
            $child = $suspensions->suspend($child, $data['reason']);
        } else {
            $child = $workflow->transition($child,$data['status'],$data['reason']??null);
            app(\App\Services\ParentNotificationService::class)->send($child,'status_'.$data['status'],'განაცხადის სტატუსი შეიცვალა','გაცნობებთ, თქვენი ბავშვის რეგისტრაციის სტატუსი შეიცვალა: '.config('statuses.application.'.$data['status'], $data['status']));
        }
        $this->logAudit('application.status',Kindergartener::class,$child->id,'Application status changed',['from'=>$from,'to'=>$data['status'],'reason'=>$data['reason']??null],true);
        return back()->with(['flashType'=>'success','flashMessage'=>'სტატუსი განახლდა.']);
        }, 3);
    }
}
