<?php
namespace App\Http\Controllers;
use App\Model\Attendance;
use App\Model\ReinstatementRequest;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ReinstatementController extends Controller
{
    public function show($token){$reinstatement=$this->byToken($token);return view('parent.reinstatement',compact('reinstatement','token'));}
    public function store(Request $request,$token)
    {
        $item=$this->byToken($token);
        abort_if($item->expires_at->isPast()||$item->status!=='pending'||$item->document_path,410,'მოთხოვნის ვადა ამოიწურა ან დოკუმენტი უკვე მიღებულია.');
        $data=$request->validate(['document'=>['required','file','mimes:pdf,jpg,jpeg,png','max:10240']]);
        $file=$data['document'];
        $path=$file->store('reinstatement-documents','local');
        $item->update(['document_path'=>$path,'original_filename'=>mb_substr($file->getClientOriginalName(),0,255)]);
        return view('parent.reinstatement-result');
    }
    public function index(){return view('reinstatement.index',['requests'=>ReinstatementRequest::with('kindergartener.kindergarten')->latest()->paginate(30)]);}
    public function download($id){$item=ReinstatementRequest::findOrFail($id);abort_unless($item->document_path&&Storage::disk('local')->exists($item->document_path),404);return Storage::disk('local')->download($item->document_path,$item->original_filename);}
    public function review(Request $request,$id,ApplicationWorkflowService $workflow)
    {
        $data=$request->validate(['decision'=>['required','in:approved,rejected'],'review_note'=>['nullable','string','max:500']]);
        $item=ReinstatementRequest::with('kindergartener')->findOrFail($id);
        abort_unless($item->status==='pending'&&$item->expires_at->isFuture()&&$item->document_path,422);
        if($data['decision']==='approved'){
            Attendance::where('kindergartener_id',$item->kindergartener_id)->whereBetween('attendance_date',[$item->absence_from,$item->absence_to])->where('status','absent')->update(['status'=>'excused']);
            $workflow->transition($item->kindergartener,'enrolled','Excused absence document approved');
        }else{$workflow->transition($item->kindergartener,'cancelled','Reinstatement request rejected');}
        $note=trim($data['review_note']??'');
        $item->update(['status'=>$data['decision'],'reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'review_note'=>$note?:null]);
        $message=$data['decision']==='approved'?'რეგისტრაცია აღდგენილია.':'აღდგენის მოთხოვნა არ დაკმაყოფილდა.';
        if($note!=='')$message.=' ადმინისტრაციის შენიშვნა: '.$note;
        app(\App\Services\ParentNotificationService::class)->send($item->kindergartener,'reinstatement_'.$data['decision'],'აღდგენის მოთხოვნის პასუხი',$message);
        $this->logAudit('reinstatement.'.$data['decision'],ReinstatementRequest::class,$item->id,'Reinstatement reviewed',['note'=>$note?:null]);
        return back()->with(['flashType'=>'success','flashMessage'=>'გადაწყვეტილება შენახულია.']);
    }
    private function byToken($token){return ReinstatementRequest::with('kindergartener')->where('token_hash',hash('sha256',$token))->firstOrFail();}
}
