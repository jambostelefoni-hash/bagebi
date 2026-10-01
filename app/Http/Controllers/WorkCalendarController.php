<?php
namespace App\Http\Controllers;
use App\Model\Kindergarten;
use App\Model\WorkCalendarDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class WorkCalendarController extends Controller
{
    public function index(){return view('calendar.index',['days'=>WorkCalendarDay::orderByDesc('date')->paginate(40),'gardens'=>Kindergarten::orderBy('name')->pluck('name','id')]);}
    public function store(Request $request){$data=$request->validate(['date'=>['required','date'],'kindergarten_id'=>['nullable','exists:kindergartens,id'],'is_working_day'=>['required','boolean'],'label'=>['nullable','string','max:255']]);DB::transaction(function()use($data){$day=WorkCalendarDay::updateOrCreate(['date'=>$data['date'],'kindergarten_id'=>$data['kindergarten_id']??null],$data);$this->logAudit('calendar.update',WorkCalendarDay::class,$day->id,'Work calendar updated',$data,true);});return back()->with(['flashType'=>'success','flashMessage'=>'კალენდარი განახლდა.']);}
    public function destroy($id){DB::transaction(function()use($id){$day=WorkCalendarDay::whereKey($id)->lockForUpdate()->firstOrFail();$details=$day->only(['date','kindergarten_id','is_working_day','label']);$day->delete();$this->logAudit('calendar.delete',WorkCalendarDay::class,(int)$id,'Work calendar entry deleted',$details,true);});return back()->with(['flashType'=>'success','flashMessage'=>'კალენდრის ჩანაწერი წაიშალა.']);}
}
