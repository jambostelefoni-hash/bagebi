<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Model\Priority;
use App\Model\KindergartnerPriority;

class PriorityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $model = Priority::all();
        return view('prioriteties.list', ['model' => $model]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('priorities')->ignore($request->id)
            ]
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        };

        $model = Priority::firstOrNew(['id' => $request->id]);
        $model->fill($request->all());
        $isNew = !$model->exists;
        $model->save();
        $this->logAudit($isNew ? 'priority.create' : 'priority.update',Priority::class,$model->id,'Priority saved',['name'=>$model->name]);

        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'პრიორიტეტი დაემატა წარმატებით'
        ];

        return back()->withInput()->withErrors([])->with($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        //
        $model = Priority::firstOrNew(['id' => $id]);

        return view('prioriteties.modify')->withModel($model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        if (!isset($id)) return back();

        $model = Priority::findOrFail($id);
        if (KindergartnerPriority::where('priority_id',$id)->exists()) return back()->withErrors(['priority'=>'პრიორიტეტი გამოიყენება აღსაზრდელის განაცხადში და მისი წაშლა შეუძლებელია.']);
        $details=['name'=>$model->name];
        $model->delete();
        $this->logAudit('priority.delete',Priority::class,(int)$id,'Priority deleted',$details);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'პრიორიტეტი წაიშალა წარმატებით'
        ];
        return back()->with($message);
    }
}













