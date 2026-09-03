<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Session;

use App\Model\Region;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $model = Region::all();
        return view('regions.list', ['model' => $model]);
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
                Rule::unique('regions')->ignore($request->id)
            ]
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        };

        $model = Region::firstOrNew(['id' => $request->id]);
        $model->fill($request->all());
        $model->save();

        $insertOrUpdate = $request->id ? 'განახლდა' : 'დაემატა';

        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'რეგიონი '. $insertOrUpdate .' წარმატებით'
        ];

        return redirect()->route('regions.list')->withInput()->withErrors([])->with($message);
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
        $model = Region::firstOrNew(['id' => $id]);

        return view('regions.modify')->withModel($model);
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

        $model = Region::destroy($id);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'რეგიონი წაიშალა წარმატებით'
        ];
        return redirect()->route('regions.list')->with($message);
    }
}













