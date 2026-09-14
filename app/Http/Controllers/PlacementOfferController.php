<?php
namespace App\Http\Controllers;
use App\Model\PlacementOffer;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PlacementOfferController extends Controller
{
    public function show($token){$offer=$this->offer($token);return view('parent.offer',compact('offer','token'));}
    public function respond(Request $request,$token,ApplicationWorkflowService $workflow)
    {
        $data=$request->validate(['response'=>['required','in:accepted,declined']]);
        DB::transaction(function()use($token,$data,$workflow){$offer=PlacementOffer::with('entry.kindergartener')->where('token_hash',hash('sha256',$token))->lockForUpdate()->firstOrFail();abort_if($offer->responded_at||$offer->expires_at->isPast(),410,'შეთავაზების ვადა ამოიწურა.');$offer->update(['responded_at'=>now(),'response'=>$data['response']]);if($data['response']==='accepted'){$workflow->acceptReservedPlacement($offer->entry->kindergartener,$offer->entry);}else{DB::table('kindergarten_group_age_range')->where('kindergarten_id',$offer->entry->kindergarten_id)->where('group_age_range',$offer->entry->group_id)->where('space_reserved','>',0)->decrement('space_reserved');$offer->entry->update(['state'=>'declined']);}},3);
        return view('parent.offer-result',['accepted'=>$data['response']==='accepted']);
    }
    private function offer($token){return PlacementOffer::with('entry.kindergartener')->where('token_hash',hash('sha256',$token))->firstOrFail();}
}
