<?php
namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class ProfileController extends Controller
{
    public function edit(Request $request){return view('profile.edit',['model'=>$request->user()]);}
    public function update(Request $request)
    {
        $user=$request->user();
        $data=$request->validate(['name'=>['required','string','max:255'],'email'=>['required','email','max:255',Rule::unique('users')->ignore($user->id)],'password'=>['nullable','string','min:8','confirmed']]);
        $user->fill(['name'=>$data['name'],'email'=>$data['email']]);
        if(!empty($data['password']))$user->password=Hash::make($data['password']);
        $changes=$this->buildAuditChanges($user);unset($changes['password']);$user->save();
        $this->logAudit('profile.update',User::class,$user->id,'Profile updated',$changes);
        return back()->with(['flashType'=>'success','flashMessage'=>'ანგარიშის მონაცემები განახლდა.']);
    }
}
