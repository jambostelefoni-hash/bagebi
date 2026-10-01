<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Model\Kindergarten;
use App\Model\Municipality;
use App\Model\GroupAgeRange;
use App\Model\API\Kindergartener;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $model = Kindergarten::with(['municipality', 'groupAgeRanges'])
            ->withCount(['occupiedChildren'])->orderBy('name')->get();
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
                'required', 'exists:municipalities,id'
            ],
            'range' => ['required', 'array'],
            'range.*.space_length' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        };        

        $model = DB::transaction(function () use ($request) {
            $model = $request->id ? Kindergarten::whereKey($request->id)->lockForUpdate()->firstOrFail() : new Kindergarten();
            $before = $model->exists ? $model->only(['name','municipality_id']) : [];
            $model->fill($request->only(['name', 'municipality_id']));
            $model->save();

            $ranges = [];
            $existing = DB::table('kindergarten_group_age_range')->where('kindergarten_id', $model->id)->lockForUpdate()->get();
            $submitted = $request->input('range', []);
            foreach ($existing as $row) {
                if (!array_key_exists($row->group_age_range, $submitted)) {
                    $submitted[$row->group_age_range] = ['space_length' => $row->space_length];
                }
            }
            foreach ($submitted as $groupId => $values) {
                if (!GroupAgeRange::whereKey($groupId)->exists()) {
                    throw ValidationException::withMessages(['range' => 'ასაკობრივი ჯგუფი არ არსებობს.']);
                }
                $capacity = (int) ($values['space_length'] ?? 0);
                $occupied = Kindergartener::where('kindergarten_id', $model->id)
                    ->where('group_id', $groupId)
                    ->whereIn('application_status', ['enrolled', 'suspended'])->count();
                $reserved = (int) DB::table('kindergarten_group_age_range')
                    ->where('kindergarten_id', $model->id)->where('group_age_range', $groupId)
                    ->value('space_reserved');
                if ($capacity < $occupied + $reserved) {
                    throw ValidationException::withMessages(['range' => 'ჯგუფის ზღვარი დაკავებულ და დაჯავშნილ ადგილებზე ნაკლები ვერ იქნება.']);
                }
                $ranges[$groupId] = ['space_length'=>$capacity,'space_filled'=>$occupied,'space_free'=>$capacity-$occupied,'space_reserved'=>$reserved];
            }
            $model->groupAgeRanges()->syncWithoutDetaching($ranges);
            $model = $model->fresh();
            $changes = $this->buildAuditChangesFromValues($before, $model->only(['name','municipality_id']));
            $changes['capacities'] = [
                'old' => $existing->mapWithKeys(fn ($row) => [(string)$row->group_age_range => (int)$row->space_length])->all(),
                'new' => collect($ranges)->mapWithKeys(fn ($values, $groupId) => [(string)$groupId => (int)$values['space_length']])->all(),
            ];
            $this->logAudit($request->id ? 'kindergarten.update' : 'kindergarten.create', Kindergarten::class, $model->id, 'Kindergarten saved', $changes, true);
            return $model;
        }, 3);

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

        $model = Kindergarten::findOrFail($id);
        if (Kindergartener::where('kindergarten_id', $id)->exists() || User::where('kindergarten_id', $id)->exists()) {
            return back()->withErrors(['kindergarten' => 'ბაღის წაშლამდე გადაიყვანეთ მასთან დაკავშირებული ბავშვები და დირექტორები.']);
        }
        $details = ['name'=>$model->name,'municipality_id'=>$model->municipality_id];
        $model->groupAgeRanges()->detach();
        $model->delete();
        $this->logAudit('kindergarten.delete', Kindergarten::class, $id, 'Kindergarten deleted', $details);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'ბაღი წაიშალა წარმატებით'
        ];
        return redirect()->route('kindergartens.list')->with($message);
    }
}








