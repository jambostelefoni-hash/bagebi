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
use App\Model\WaitingListEntry;

use App\Exports\KindergartenerExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ApplicationWorkflowService;
use Carbon\Carbon;


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
        $model = Kindergartener::with('municipality', 'kindergarten', 'groupRange', 'priority', 'activeStatus')
            ->when(auth()->user()->role === 'director', fn ($q) => $q->where('kindergarten_id', auth()->user()->kindergarten_id))
            ->latest()->get();
        return view('kindergarteners.list', ['model' => $model]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function publicStore(Request $request)
    {
        $registrationOpen = (bool) data_get(Setting::where('slug', 'basic')->first(), 'object.isRegistrationStart', false);
        if (!$registrationOpen) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'registration' => 'რეგისტრაცია ამჟამად დახურულია.',
            ]);
        }
        $request->request->remove('id');
        $request->request->remove('active_status_id');
        $request->request->remove('has_permission');
        return $this->store($request, app(ApplicationWorkflowService::class));
    }

    public function store(Request $request, ApplicationWorkflowService $workflow)
{
    if (auth()->check() && auth()->user()->role === 'director') {
        $garden = Kindergarten::findOrFail(auth()->user()->kindergarten_id);
        $request->merge(['kindergarten_id' => $garden->id, 'municipality_id' => $garden->municipality_id]);
        if ($request->filled('id')) abort_unless((int)Kindergartener::findOrFail($request->id)->kindergarten_id === (int)$garden->id, 403);
        $request->request->remove('priority_id');
        $request->request->remove('has_permission');
    }
    $kindergartener = Kindergartener::firstOrNew(['id' => $request->id]);
    $isNew = !$request->filled('id');

    $validator = Validator::make($request->all(), [
        'municipality_id' => ['required'],
        'kindergarten_id' => ['required'],
        'group_id' => ['required'],
        'birth_date' => ['required', 'date', function ($attribute, $value, $fail) {
            $setting = Setting::where('slug', 'date')->first();
            $startValue = data_get($setting, 'object.start');
            if (!$startValue) return $fail('სასწავლო წლის დაწყების თარიღი არ არის მითითებული.');
            try {
                $start = Carbon::parse($startValue);
                $birth = Carbon::parse($value);
                if ($birth->gt($start->copy()->subYears(2)) || !$birth->gt($start->copy()->subYears(6))) {
                    $fail('სასწავლო წლის დაწყებისას ბავშვი უნდა იყოს 2-დან 6 წლამდე.');
                }
            } catch (\Throwable $e) {
                $fail('დაბადების თარიღი არასწორია.');
            }
        }],
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
        'email' => ['required', 'email'],
        'priority_id' => ['nullable', 'exists:priorities,id'],  // 👈 პრივილეგიის ვალიდაცია
        'has_permission' => ['nullable', 'boolean'],            // 👈 დადასტურების ვალიდაცია
    ]);

    $kindergarten = Kindergarten::find($request->kindergarten_id);
    $kindergartenAgeRange = $kindergarten ? $kindergarten->currentAge($request->group_id) : null;
    $placementChanged = !$kindergartener->exists
        || (int) $kindergartener->kindergarten_id !== (int) $request->kindergarten_id
        || (int) $kindergartener->group_id !== (int) $request->group_id;

    $validator->after(function ($validator) use ($kindergartenAgeRange, $placementChanged) {
        if ($placementChanged && !$kindergartenAgeRange) {
            $validator->errors()->add('group_id', 'არჩეული ჯგუფი ამ ბაღში არ არის გააქტიურებული. ჯერ ბაღის ტევადობაში მიუთითეთ ჯგუფის ადგილების რაოდენობა.');
        }
    });

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['errors' => $validator->errors()->all(), 'status' => 'errors']);
        } else {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    };

    $kindergartener = $workflow->save($request->all(), $kindergartener->exists ? $kindergartener : null);
    $changes = ['application_status' => $kindergartener->application_status];
    if ($isNew) {
        app(\App\Services\ParentNotificationService::class)->send(
            $kindergartener,
            'application_created',
            'განაცხადი მიღებულია',
            'თქვენი განაცხადი მიღებულია. მიმდინარე სტატუსი: '.$kindergartener->application_status_label
        );
    }

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

    $kindergartener->load('priority');
    $workflow->syncWaitingPriority($kindergartener);

    $action = $isNew ? 'kindergartener.create' : 'kindergartener.update';
    $this->logAudit($action, Kindergartener::class, $kindergartener->id, 'Kindergartener saved', $changes);

    $insertOrUpdate = $request->id ? 'განახლდა' : 'დაემატა';

    $message = [
        'flashType'    => 'success',
        'flashMessage' => 'აღსაზრდელის ინფორმაცია '. $insertOrUpdate .' წარმატებით'
    ];

    if ($request->ajax()) {
        $queuePosition = null;
        if ($kindergartener->application_status === ApplicationWorkflowService::WAITING) {
            $entry = WaitingListEntry::where('kindergartener_id', $kindergartener->id)->where('state', 'waiting')->first();
            if ($entry) {
                $queuePosition = WaitingListEntry::where('kindergarten_id', $entry->kindergarten_id)
                    ->where('group_id', $entry->group_id)
                    ->where('state', 'waiting')
                    ->where(function ($query) use ($entry) {
                        $query->where('priority_rank', '<', $entry->priority_rank)
                            ->orWhere(function ($query) use ($entry) {
                                $query->where('priority_rank', $entry->priority_rank)
                                    ->where(function ($query) use ($entry) {
                                        $query->where('queued_at', '<', $entry->queued_at)
                                            ->orWhere(function ($query) use ($entry) {
                                                $query->where('queued_at', $entry->queued_at)->where('id', '<', $entry->id);
                                            });
                                    });
                            });
                    })->count() + 1;
            }
        }
        return response()->json([
            'message' => $message,
            'status' => 'success',
            'application_status' => $kindergartener->application_status,
            'application_status_label' => $kindergartener->application_status_label,
            'queue_position' => $queuePosition,
        ]);
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
        $model = Kindergartener::firstOrNew(['id' => $id]);
        $user = auth()->user();
        if ($model->exists && $user->role === 'director') {
            abort_unless((int)$model->kindergarten_id === (int)$user->kindergarten_id, 403);
        }

        if ($user->role === 'director') {
            $garden = Kindergarten::with(['municipality', 'groupAgeRanges'])->findOrFail($user->kindergarten_id);
            $municipalities = Municipality::with(['kindergartens' => fn ($query) => $query->whereKey($garden->id)])
                ->whereKey($garden->municipality_id)->get();
            $groupRanges = $garden->groupAgeRanges->pluck('range', 'id');
        } else {
            $garden = null;
            $municipalities = Municipality::with('kindergartens')->get();
            $groupRanges = GroupAgeRange::pluck('range', 'id');
        }

        $data = [
          'municipalities' => $municipalities,
          'group_ranges' => $groupRanges,
          'priorities' => Priority::pluck('name', 'id'),
          'assigned_garden' => $garden,

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
    public function destroy($id, ApplicationWorkflowService $workflow)
    {
        //
        if (!isset($id)) return back();

        $model = Kindergartener::find($id);
        $details = $model
            ? ['name' => $model->kids_first_name.' '.$model->kids_last_name, 'kids_personal_number' => $model->kids_personal_number]
            : null;
        if ($model) {
            if (in_array($model->application_status, [ApplicationWorkflowService::ENROLLED, ApplicationWorkflowService::SUSPENDED], true)) {
                $workflow->transition($model, ApplicationWorkflowService::CANCELLED, 'Record deleted');
            }
            $model->delete();
        }
        $this->logAudit('kindergartener.delete', Kindergartener::class, $id, 'Kindergartener deleted', $details);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'აღსაზრდელი წაიშალა ბაზიდან!'
        ];
        return redirect()->route('kindergarteners.index')->with($message);
    }


    public function order(Request $request, ApplicationWorkflowService $workflow)
    {
       $errs = [];
       if ($request->missing('ids')) { $errs = Arr::prepend($errs, 'მონიშნეთ აღსაზრდელი/არსაზრდელები');};
       if (!$request->filled('action')) { $errs = Arr::prepend($errs, 'მართვის ველი ცარიელია');};
       if (!$request->filled('destination')) { $errs = Arr::prepend($errs, 'ცვლილების ველი ცარიელია');};
       
       if ($errs) return redirect()->route('kindergarteners.index')->withErrors($errs);
       $list = Kindergartener::whereIn('id', $request->ids)
           ->when(auth()->user()->role === 'director', fn ($q) => $q->where('kindergarten_id', auth()->user()->kindergarten_id))->get();
       abort_unless($list->count() === count($request->ids), 403);

       $action = $request->action;
       $destination = $request->destination;

       $list->each(function ($item, $key) use($action,$destination,$workflow) {
          if($action == 1) {
            if ($item->priority !== null) {
              $item->priority()->update(['has_permission' => $destination]);
              $item->load('priority');
              $workflow->syncWaitingPriority($item);
            }
          } else if($action == 2) {
            $statuses = config('statuses.application', []);
            abort_unless(isset($statuses[$destination]) && $destination !== ApplicationWorkflowService::GRADUATED, 422);
            if (auth()->user()->role === 'director') {
                abort_unless($item->application_status === ApplicationWorkflowService::ENROLLED && $destination === ApplicationWorkflowService::SUSPENDED, 403);
            }
            $workflow->transition($item, $destination, 'Bulk status update');
          }
          if($action == 1) $item->save();
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
         $request->validate(['kids_personal_number' => ['required', 'digits:11']]);
         $kid = Kindergartener::with(['kindergarten:id,name', 'groupRange:id,range'])
                ->where('kids_personal_number', $request->kids_personal_number)
                ->first();

       return response()->json(['data' => $kid ? [
           'application_status' => $kid->application_status,
           'application_status_label' => $kid->application_status_label,
           'kindergarten' => optional($kid->kindergarten)->name,
           'group' => optional($kid->groupRange)->range,
       ] : null, 'status' => 'success']);
    }


    public function dataObject()
    {
        $municipalities = Municipality::with('kindergartens.groupAgeRanges')->get();
        $waitingCounts = \App\Model\WaitingListEntry::query()
            ->where('state', 'waiting')
            ->selectRaw('kindergarten_id, group_id, COUNT(*) as total')
            ->groupBy('kindergarten_id', 'group_id')
            ->get()
            ->keyBy(fn ($entry) => $entry->kindergarten_id.':'.$entry->group_id);
        $municipalities->each(function ($municipality) use ($waitingCounts) {
            $municipality->kindergartens->each(function ($garden) use ($waitingCounts) {
                $garden->groupAgeRanges->each(function ($range) use ($waitingCounts) {
                    $range->pivot->space_free = max(0, (int)$range->pivot->space_free - (int)($range->pivot->space_reserved ?? 0));
                    $range->pivot->waiting_count = (int) data_get($waitingCounts->get($range->pivot->kindergarten_id.':'.$range->id), 'total', 0);
                });
            });
        });
        return [
          'priorities' => Priority::all(),
          'setting' => Setting::where(['slug' => 'basic'])->firstOrNew()->toArray(),
          'learning_start_date' => data_get(Setting::where('slug', 'date')->first(), 'object.start'),
          'municipalities' => $municipalities
        ];
    }

    public function export() 
    {
        return Excel::download(new KindergartenerExport(auth()->user()->role === 'director' ? auth()->user()->kindergarten_id : null), 'kindergarteners.xlsx');
    }
}











