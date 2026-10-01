<?php
namespace App\Http\Controllers;
use App\Model\Attendance;
use App\Model\ReinstatementRequest;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ReinstatementController extends Controller
{
    public function show($token){$reinstatement=$this->byToken($token, true);return view('parent.reinstatement',compact('reinstatement','token'));}
    public function store(Request $request,$token)
    {
        $this->byToken($token, true);
        $data=$request->validate(['document'=>['required','file','mimes:pdf,jpg,jpeg,png','max:10240']]);
        $file=$data['document'];
        $path=$file->store('reinstatement-documents','local');
        abort_unless($path, 500);
        try {
        Storage::disk('local')->setVisibility($path, 'private');
        DB::transaction(function () use ($token, $file, $path) {
        $item=ReinstatementRequest::where('token_hash',hash('sha256',$token))->lockForUpdate()->firstOrFail();
        abort_if($item->expires_at->isPast()||!in_array($item->status,['pending','needs_correction'],true)||($item->document_path&&$item->status!=='needs_correction'),410,'მოთხოვნის ვადა ამოიწურა ან დოკუმენტი უკვე მიღებულია.');
        $item->update(['document_path'=>$path,'original_filename'=>mb_substr($file->getClientOriginalName(),0,255),'status'=>'pending','reviewed_by'=>null,'reviewed_at'=>null]);
        });
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($path);
            throw $error;
        }
        return view('parent.reinstatement-result');
    }
    public function index(Request $request){$requests=ReinstatementRequest::with('kindergartener.kindergarten')->when($request->status,fn($q,$status)=>$q->where('status',$status))->when($request->document==='uploaded',fn($q)=>$q->whereNotNull('document_path'))->when($request->document==='missing',fn($q)=>$q->whereNull('document_path'))->when($request->kindergarten_id,fn($q,$id)=>$q->whereHas('kindergartener',fn($children)=>$children->where('kindergarten_id',$id)))->latest()->paginate(30);return view('reinstatement.index',['requests'=>$requests,'gardens'=>\App\Model\Kindergarten::orderBy('name')->pluck('name','id')]);}
    public function download($id){$item=ReinstatementRequest::findOrFail($id);abort_unless($item->document_path&&Storage::disk('local')->exists($item->document_path),404);return Storage::disk('local')->download($item->document_path,$item->original_filename);}
    public function review(Request $request,$id,ApplicationWorkflowService $workflow)
    {
        $data=$request->validate(['decision'=>['required','in:approved,rejected,needs_correction'],'review_note'=>['nullable','string','max:500']]);
        if (in_array($data['decision'], ['rejected','needs_correction'], true) && trim($data['review_note']??'')==='') return back()->withErrors(['review_note'=>'მიუთითეთ გადაწყვეტილების მიზეზი.']);
        $item = DB::transaction(function () use ($id, $data, $workflow) {
        $item=ReinstatementRequest::with('kindergartener')->lockForUpdate()->findOrFail($id);
        abort_unless($item->status==='pending'&&$item->document_path,422);
        abort_unless($item->kindergartener->application_status==='suspended',422);
        if($data['decision']==='approved'){
            Attendance::where('kindergartener_id',$item->kindergartener_id)->whereBetween('attendance_date',[$item->absence_from,$item->absence_to])->where('status','absent')->update(['status'=>'excused']);
            $workflow->transition($item->kindergartener,'enrolled','Excused absence document approved');
        }elseif($data['decision']==='rejected'){$workflow->transition($item->kindergartener,'cancelled','Reinstatement request rejected');}
        else abort_if($item->expires_at->isPast(),422,'დოკუმენტის ხელახლა ატვირთვის ვადა ამოიწურა.');
        $note=trim($data['review_note']??'');
        $updates=['status'=>$data['decision'],'reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'review_note'=>$note?:null];
        if ($data['decision']==='needs_correction') {
            // Keep the request open, remove the rejected file and issue a fresh upload link.
            $oldDocumentPath = $item->document_path;
            DB::afterCommit(fn () => Storage::disk('local')->delete($oldDocumentPath));
            $raw=Str::random(64);
            $updates=array_merge($updates,[
                'status'=>'needs_correction',
                'document_path'=>null,
                'original_filename'=>null,
                'token_hash'=>hash('sha256',$raw),
            ]);
        }
        DB::table('reinstatement_requests')->where('id',$item->id)->update($updates);
        $item->refresh();
        $item->setAttribute('replacement_token',$raw??null);
        $note=trim($data['review_note']??'');
        $message=$data['decision']==='approved'?'რეგისტრაცია აღდგენილია.':($data['decision']==='needs_correction'?'დოკუმენტი საჭიროებს დაზუსტებას. გთხოვთ, ვადის ამოწურვამდე ხელახლა ატვირთოთ სწორი დოკუმენტი.':'აღდგენის მოთხოვნა არ დაკმაყოფილდა.');
        if($note!=='')$message.=' ადმინისტრაციის შენიშვნა: '.$note;
        app(\App\Services\ParentNotificationService::class)->send($item->kindergartener->fresh(),'reinstatement_'.$data['decision'],'აღდგენის მოთხოვნის პასუხი',$message,$item->replacement_token?route('reinstatement.show',$item->replacement_token):null);
        $this->logAudit('reinstatement.'.$data['decision'],ReinstatementRequest::class,$item->id,'Reinstatement reviewed',['note'=>$note?:null],true);
        return $item;
        }, 3);
        return back()->with(['flashType'=>'success','flashMessage'=>'გადაწყვეტილება შენახულია.']);
    }
    private function byToken($token, bool $mustBeActive = false){$request=ReinstatementRequest::with('kindergartener')->where('token_hash',hash('sha256',$token))->firstOrFail();if($mustBeActive)abort_if($request->expires_at->isPast()||!in_array($request->status,['pending','needs_correction'],true),410);return $request;}
}
