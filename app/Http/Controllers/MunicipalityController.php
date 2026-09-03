<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Model\Municipality;
use App\Model\Region;

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
        $model->save();

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

        $model = Municipality::destroy($id);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'რეგიონი წაიშალა წარმატებით'
        ];
        return redirect()->route('municipalities.list')->with($message);
    }
}







