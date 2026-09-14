<?php
namespace App\Http\Controllers;
use App\Model\Kindergarten;
use App\Model\WorkCalendarDay;
use Illuminate\Http\Request;
class WorkCalendarController extends Controller
{
    public function index(){return view('calendar.index',['days'=>WorkCalendarDay::orderByDesc('date')->paginate(40),'gardens'=>Kindergarten::orderBy('name')->pluck('name','id')]);}
    public function store(Request $request){$data=$request->validate(['date'=>['required','date'],'kindergarten_id'=>['nullable','exists:kindergartens,id'],'is_working_day'=>['required','boolean'],'label'=>['nullable','string','max:255']]);WorkCalendarDay::updateOrCreate(['date'=>$data['date'],'kindergarten_id'=>$data['kindergarten_id']??null],$data);$this->logAudit('calendar.update',WorkCalendarDay::class,null,'Work calendar updated',$data);return back()->with(['flashType'=>'success','flashMessage'=>'კალენდარი განახლდა.']);}
    public function destroy($id){$day=WorkCalendarDay::findOrFail($id);$details=$day->only(['date','kindergarten_id','is_working_day','label']);$day->delete();$this->logAudit('calendar.delete',WorkCalendarDay::class,(int)$id,'Work calendar entry deleted',$details);return back()->with(['flashType'=>'success','flashMessage'=>'კალენდრის ჩანაწერი წაიშალა.']);}
}
