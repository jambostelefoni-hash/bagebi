<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Model\API\Kindergartener;

use App\Model\Setting;
use App\Model\Priority;
use App\Model\Municipality;
use App\Model\Kindergarten;
use App\Model\GroupAgeRange;
use App\Model\ActiveStatus;
use App\Model\KindergartnerPriority;

use App\Exports\KindergartenerExport;
use Maatwebsite\Excel\Facades\Excel;


use Arr;

class KindergartenerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $model = Kindergartener::with('municipality', 'kindergarten', 'groupRange', 'priority', 'activeStatus')->get();
        return view('kindergarteners.list', ['model' => $model]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $kindergartener = Kindergartener::firstOrNew(['id' => $request->id]);
    $isNew = !$request->filled('id');

    $validator = Validator::make($request->all(), [
        'municipality_id' => ['required'],
        'kindergarten_id' => ['required'],
        'group_id' => ['required'],
        'kids_personal_number' => [
            'required',
            'numeric',
            'digits:11',
            Rule::unique('kindergarteners')->ignore($request->id)
        ],
        'kids_first_name' => ['required', 'alpha'],
        'kids_last_name' => ['required', 'alpha'],
        'mother_personal_number' => ['nullable','numeric','digits:11'],
        'mother_first_name' => ['nullable', 'alpha'],
        'mother_last_name' => ['nullable', 'alpha'],
        'father_personal_number' => ['nullable','numeric','digits:11'],
        'father_first_name' => ['nullable', 'alpha'],
        'father_last_name' => ['nullable', 'alpha'],
        'mobile_number' => ['required', 'numeric', 'digits:9'],
        'email' => ['nullable', 'email'],
        'priority_id' => ['nullable', 'exists:priorities,id'],  // 👈 პრივილეგიის ვალიდაცია
        'has_permission' => ['nullable', 'boolean'],            // 👈 დადასტურების ვალიდაცია
    ]);

    $kindergarten = Kindergarten::find($request->kindergarten_id);
    $kindergartenAgeRange = $kindergarten ? $kindergarten->currentAge($request->group_id) : null;

    $validator->after(function ($validator) use ($kindergartenAgeRange,$request) {
        if (!$kindergartenAgeRange) {
            $validator->errors()->add('kindergarten_id', 'შეავსეთ ყველა აუცილებელი ველი!');
        }
        else if ($kindergartenAgeRange->pivot->space_free == 0 && !$request->filled('id')) {
            $validator->errors()->add('kindergarten_id', 'ბაღში თავისუფალი ადგილი არ არის!');
        }
    });

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['errors' => $validator->errors()->all(), 'status' => 'errors']);
        } else {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    };

    // ვავსებთ ძირითად ინფორმაციას
    $kindergartener->fill($request->all());
    $changes = $this->buildAuditChanges($kindergartener);
    $kindergartener->save();

    // ვამუშავებთ პრივილეგიას
    if ($request->filled('priority_id')) {
        if ($kindergartener->priority) {
            // უკვე არსებობს => ვაახლებთ
            $kindergartener->priority->fill([
                'priority_id'    => $request->priority_id,
                'has_permission' => $request->has_permission ?? 0
            ]);
            $kindergartener->priority->save();
        } else {
            // ახალი პრივილეგია
            $priority = new KindergartnerPriority([
                'priority_id'    => $request->priority_id,
                'has_permission' => $request->has_permission ?? 0
            ]);
            $kindergartener->priority()->save($priority);
        }
    } else {
        // თუ საერთოდ მოხსნეს პრივილეგია
        if ($kindergartener->priority) {
            $kindergartener->priority()->delete();
        }
    }

    // ჯგუფის ადგილი - სივრცის განახლება
    $newData = [
        'space_filled' => $kindergartenAgeRange->pivot->space_filled + 1,
        'space_free' => $kindergartenAgeRange->pivot->space_free - 1
    ];
    $kindergarten->groupAgeRanges()->updateExistingPivot($request->group_id, $newData);

    $action = $isNew ? 'kindergartener.create' : 'kindergartener.update';
    $this->logAudit($action, Kindergartener::class, $kindergartener->id, 'Kindergartener saved', $changes);

    $insertOrUpdate = $request->id ? 'განახლდა' : 'დაემატა';

    $message = [
        'flashType'    => 'success',
        'flashMessage' => 'აღსაზრდელის ინფორმაცია '. $insertOrUpdate .' წარმატებით'
    ];

    if ($request->ajax()) {
        return response()->json(['message' => $message, 'status' => 'success']);
    } else {
        return back()->withInput()->withErrors([])->with($message);
    }
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
        $model = Kindergartener::firstOrNew(['id' => $id]);
        $data = [
          'municipalities' => Municipality::with('kindergartens')->get(),
          'group_ranges' => GroupAgeRange::pluck('range', 'id'),
          'active_statuses' => ActiveStatus::pluck('name', 'id'),
          'priorities' => Priority::pluck('name', 'id'),

        ];

        return view('kindergarteners.modify')->withModel($model)->withData($data);
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

        $model = Kindergartener::find($id);
        $details = $model
            ? ['name' => $model->kids_first_name.' '.$model->kids_last_name, 'kids_personal_number' => $model->kids_personal_number]
            : null;
        Kindergartener::destroy($id);
        $this->logAudit('kindergartener.delete', Kindergartener::class, $id, 'Kindergartener deleted', $details);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'აღსაზრდელი წაიშალა ბაზიდან!'
        ];
        return redirect()->route('kindergarteners.index')->with($message);
    }


    public function order(Request $request)
    {
       $errs = [];
       if ($request->missing('ids')) { $errs = Arr::prepend($errs, 'მონიშნეთ აღსაზრდელი/არსაზრდელები');};
       if (!$request->filled('action')) { $errs = Arr::prepend($errs, 'მართვის ველი ცარიელია');};
       if (!$request->filled('destination')) { $errs = Arr::prepend($errs, 'ცვლილების ველი ცარიელია');};
       
       if ($errs) return redirect()->route('kindergarteners.index')->withErrors($errs);
       $list = Kindergartener::find($request->ids);

       $action = $request->action;
       $destination = $request->destination;

       $list->each(function ($item, $key) use($action,$destination) {
          if($action == 1) {
            if ($item->priority !== null) {
              $item->priority()->update(['has_permission' => $destination]);
            }
          } else if($action == 2) {
            $group_range = $item->groupRange;
            $garden = $item->kindergarten;
            $gardenByGroupAge = $garden->currentAge($group_range->id);
            if ($destination == 4) {
               $newData = [
                  'space_filled' => $gardenByGroupAge->pivot->space_filled > 0 ? $gardenByGroupAge->pivot->space_filled - 1 : 0,
                  'space_free' => $gardenByGroupAge->pivot->space_free + 1
               ];
               $garden->groupAgeRanges()->updateExistingPivot($group_range->id, $newData);
            } else if (($destination == 1 || $destination == 2) && $item->active_status_id == 4) {
                $newData = [
                  'space_filled' => $gardenByGroupAge->pivot->space_filled + 1,
                  'space_free' => $gardenByGroupAge->pivot->space_free > 0 ? $gardenByGroupAge->pivot->space_free - 1 : 0
                ];
                if (($gardenByGroupAge->pivot->space_filled + 1) > $gardenByGroupAge->pivot->space_length) {
                    $newData['space_length'] = $gardenByGroupAge->pivot->space_length + 1;
                }
                $garden->groupAgeRanges()->updateExistingPivot($group_range->id, $newData);
            }
            $garden->save();
            if(!$item->graduate) $item->fill(['active_status_id' => $destination]);
          }
          $item->save();
       });

       $message = [
          'flashType'    => 'success',
          'flashMessage' => 'ცვლილება შესრულდა წარმატებით'
        ];

             $this->logAudit('kindergartener.bulk_action', Kindergartener::class, null, 'Bulk action applied', [
                     'action' => $action,
                     'destination' => $destination,
                     'ids' => $request->ids
             ]);

       return redirect()->route('kindergarteners.index')->with($message);
    }

    public function findKid (Request $request) {
         $kid = Kindergartener::with(['kindergarten', 'municipality', 'activeStatus'])
                ->where('kids_personal_number', $request->kids_personal_number)
                ->get();

       return response()->json(['data' => $kid, 'status' => 'success']);
    }


    public function dataObject()
    {
        return [
          'priorities' => Priority::all(),
          'setting' => Setting::where(['slug' => 'basic'])->firstOrNew()->toArray(),
          'municipalities' => Municipality::with('kindergartens.groupAgeRanges')->get()
        ];
    }

    public function export() 
    {
        return Excel::download(new KindergartenerExport, 'kindergarteners.xlsx');
    }
}











