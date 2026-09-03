<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Model\Kindergarten;
use App\Model\Municipality;
use App\Model\GroupAgeRange;

class KindergartenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $model = Kindergarten::all();
        return view('kindergartens.list', ['model' => $model]);
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
                Rule::unique('kindergartens')->where(function ($query) use ($request) {
                    return $query->where('name', $request->name)->where('municipality_id', $request->municipality_id);
                })->ignore($request->id)
            ],
            'municipality_id' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        };        

        $filterRange = array_map(function($array) {
           return ['space_free' => ($array['space_length'] - $array['space_filled'])] + $array;
        }, array_filter($request->range, function($item) {
           return !empty($item['space_length']);
        }));

        $model = Kindergarten::firstOrNew(['id' => $request->id]);
        $model->fill($request->all());
        $model->save();
        $model->groupAgeRanges()->sync($filterRange);
        $model->fresh();

        $insertOrUpdate = $request->id ? 'განახლდა' : 'დაემატა';

        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'ბაღი '. $insertOrUpdate .' წარმატებით'
        ];

        return redirect()->route('kindergartens.list')->withInput()->withErrors([])->with($message);
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
        $model = Kindergarten::firstOrNew(['id' => $id]);
        $data = [
          'municipalities' => Municipality::pluck('name', 'id'),
          'group_ranges' => GroupAgeRange::pluck('range', 'id')
        ];

        return view('kindergartens.modify')->withModel($model)->withData($data);
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

        $model = Kindergarten::destroy($id);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'ბაღი წაიშალა წარმატებით'
        ];
        return redirect()->route('kindergartens.list')->with($message);
    }
}








