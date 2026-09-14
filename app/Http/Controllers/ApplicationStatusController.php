<?php
namespace App\Http\Controllers;
use App\Model\API\Kindergartener;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\Request;
class ApplicationStatusController extends Controller
{
    public function update(Request $request,$id,ApplicationWorkflowService $workflow)
    {
        $data=$request->validate(['status'=>['required','in:registered,waiting,enrolled,suspended,cancelled,graduated'],'reason'=>['nullable','string','max:500']]);
        $child=Kindergartener::findOrFail($id);$user=$request->user();
        if(!$user->isUnionAdmin()){
            abort_unless((int)$child->kindergarten_id===(int)$user->kindergarten_id,403);
            abort_unless($child->application_status==='enrolled'&&$data['status']==='suspended',403);
        }
        $from = $child->application_status;
        $workflow->transition($child,$data['status'],$data['reason']??null);
        app(\App\Services\ParentNotificationService::class)->send($child,'status_'.$data['status'],'განაცხადის სტატუსი შეიცვალა','თქვენი განაცხადის ახალი სტატუსია: '.config('statuses.application.'.$data['status'], $data['status']));
        $this->logAudit('application.status',Kindergartener::class,$child->id,'Application status changed',['from'=>$from,'to'=>$data['status'],'reason'=>$data['reason']??null]);
        return back()->with(['flashType'=>'success','flashMessage'=>'სტატუსი განახლდა.']);
    }
}
