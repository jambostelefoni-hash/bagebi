<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Model\Municipality;
use App\Model\Region;
use App\Model\Kindergarten;
use App\Model\API\Kindergartener;

class MunicipalityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $model = Municipality::all();
        return view('municipalities.list', ['model' => $model]);
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
                Rule::unique('municipalities')->ignore($request->id)
            ],
            'region_id' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        };

        $model = Municipality::firstOrNew(['id' => $request->id]);
        $model->fill($request->all());
        $isNew = !$model->exists;
        $model->save();
        $this->logAudit($isNew ? 'municipality.create' : 'municipality.update',Municipality::class,$model->id,'Municipality saved',['name'=>$model->name,'region_id'=>$model->region_id]);

        $saveOrUpdate = $request->id ? 'განახლდა' : 'დაემატა';

        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'მუნიციპალიტეტი '. $saveOrUpdate .' წარმატებით'
        ];

        return redirect()->route('municipalities.list')->withInput()->withErrors([])->with($message);
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
        $model = Municipality::firstOrNew(['id' => $id]);
        $data = [
          'regions' => Region::pluck('name', 'id')
        ];

        return view('municipalities.modify')->withModel($model)->withData($data);
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

        $model = Municipality::findOrFail($id);
        if (Kindergarten::where('municipality_id',$id)->exists() || Kindergartener::where('municipality_id',$id)->exists()) return back()->withErrors(['municipality'=>'მუნიციპალიტეტის წაშლამდე გადაიტანეთ მასთან დაკავშირებული ბაღები და აღსაზრდელები.']);
        $details=['name'=>$model->name,'region_id'=>$model->region_id];
        $model->delete();
        $this->logAudit('municipality.delete',Municipality::class,(int)$id,'Municipality deleted',$details);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'მუნიციპალიტეტი წაიშალა წარმატებით'
        ];
        return redirect()->route('municipalities.list')->with($message);
    }
}





