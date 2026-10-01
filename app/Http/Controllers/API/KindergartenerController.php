<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\DB;

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
use App\Services\SuspensionWorkflowService;
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
        $total = Kindergartener::when(auth()->user()->role === 'director', fn ($query) => $query->where('kindergarten_id', auth()->user()->kindergarten_id))->count();
        return view('kindergarteners.list', compact('total'));
    }

    public function dataTable(Request $request)
    {
        $base = Kindergartener::query()
            ->when(auth()->user()->role === 'director', fn ($query) => $query->where('kindergarten_id', auth()->user()->kindergarten_id));
        $total = (clone $base)->count();
        $search = trim((string) data_get($request->input('search'), 'value'));
        if ($search !== '') {
            $base->where(function ($query) use ($search) {
                $query->where('kids_first_name', 'like', '%'.$search.'%')
                    ->orWhere('kids_last_name', 'like', '%'.$search.'%')
                    ->orWhere('kids_personal_number', 'like', '%'.$search.'%')
                    ->orWhere('application_status', 'like', '%'.$search.'%')
                    ->orWhereHas('kindergarten', fn ($garden) => $garden->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('municipality', fn ($municipality) => $municipality->where('name', 'like', '%'.$search.'%'));
            });
        }
        $filtered = (clone $base)->count();
        $columns = [0=>'id', 6=>'kids_personal_number', 7=>'kids_first_name', 8=>'birth_date', 9=>'created_at'];
        $orderColumn = $columns[(int) data_get($request->input('order'), '0.column')] ?? 'id';
        $direction = data_get($request->input('order'), '0.dir') === 'asc' ? 'asc' : 'desc';
        $length = min(100, max(10, (int) $request->input('length', 10)));
        $start = max(0, (int) $request->input('start', 0));
        $rows = $base->select(['id','municipality_id','kindergarten_id','group_id','kids_personal_number','kids_first_name','kids_last_name','birth_date','created_at','graduate','application_status'])
            ->with(['municipality:id,name','kindergarten:id,name','groupRange:id,range','priority:id,kindergartner_id,priority_id,has_permission'])
            ->orderBy($orderColumn, $direction)->offset($start)->limit($length)->get();

        return response()->json(['draw'=>(int)$request->input('draw'),'recordsTotal'=>$total,'recordsFiltered'=>$filtered,'data'=>$rows]);
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
        $request->replace(\Illuminate\Support\Arr::only($request->all(), [
            'kids_personal_number','kids_first_name','kids_last_name','birth_date',
            'mother_personal_number','mother_first_name','mother_last_name',
            'father_personal_number','father_first_name','father_last_name',
            'mobile_number','email','municipality_id','kindergarten_id','group_id','priority_id',
        ]));
        if ($request->filled('priority_id')) $request->merge(['has_permission' => false]);
        return $this->store($request, app(ApplicationWorkflowService::class));
    }

    public function checkPersonalNumber(Request $request)
    {
        $data = $request->validate([
            'kids_personal_number' => ['required', 'digits:11'],
        ]);

        return response()->json([
            'exists' => Kindergartener::where('kids_personal_number', $data['kids_personal_number'])->exists(),
        ]);
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
    $auditFields = [
        'kids_personal_number', 'kids_first_name', 'kids_last_name', 'birth_date',
        'mother_personal_number', 'mother_first_name', 'mother_last_name',
        'father_personal_number', 'father_first_name', 'father_last_name',
        'mobile_number', 'email', 'municipality_id', 'kindergarten_id', 'group_id',
        'application_status',
    ];
    $auditBefore = $kindergartener->exists
        ? \Illuminate\Support\Arr::only($kindergartener->getAttributes(), $auditFields)
        : [];
    $priorityBefore = $kindergartener->exists
        ? optional($kindergartener->priority)->only(['priority_id', 'has_permission'])
        : null;

    $validator = Validator::make($request->all(), [
        'municipality_id' => ['required', 'exists:municipalities,id'],
        'kindergarten_id' => ['required', 'exists:kindergartens,id'],
        'group_id' => ['required', 'exists:group_age_ranges,id'],
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
        'mobile_number' => ['required', 'regex:/^5\d{8}$/'],
        'email' => ['nullable', 'email'],
        'priority_id' => ['nullable', 'exists:priorities,id'],  // 👈 პრივილეგიის ვალიდაცია
        'has_permission' => ['nullable', 'boolean'],            // 👈 დადასტურების ვალიდაცია
    ], [
        'kids_personal_number.unique' => 'ამ პირადი ნომრით ბავშვი უკვე რეგისტრირებულია.',
        'mobile_number.regex' => 'მობილურის ნომერი უნდა შედგებოდეს 9 ციფრისგან და იწყებოდეს 5-ით.',
    ]);

    $kindergarten = Kindergarten::find($request->kindergarten_id);
    $kindergartenAgeRange = $kindergarten ? $kindergarten->currentAge($request->group_id) : null;
    $placementChanged = !$kindergartener->exists
        || (int) $kindergartener->kindergarten_id !== (int) $request->kindergarten_id
        || (int) $kindergartener->group_id !== (int) $request->group_id;

    $validator->after(function ($validator) use ($kindergartenAgeRange, $placementChanged, $kindergarten, $request) {
        if ($kindergarten && (int) $kindergarten->municipality_id !== (int) $request->municipality_id) {
            $validator->errors()->add('municipality_id', 'არჩეული ბაღი მითითებულ მუნიციპალიტეტს არ ეკუთვნის.');
        }
        if ($placementChanged && !$kindergartenAgeRange) {
            $validator->errors()->add('group_id', 'არჩეული ჯგუფი ამ ბაღში არ არის გააქტიურებული. ჯერ ბაღის ტევადობაში მიუთითეთ ჯგუფის ადგილების რაოდენობა.');
        }
        $range = GroupAgeRange::find($request->group_id);
        $startValue = data_get(Setting::where('slug', 'date')->first(), 'object.start');
        if ($placementChanged && $range && $startValue && preg_match('/^\s*(\d+)\s*[-–—]\s*(\d+)\s*$/u', $range->range, $matches)) {
            try {
                $age = Carbon::parse($request->birth_date)->diffInYears(Carbon::parse($startValue), false);
                if ($age < (int) $matches[1] || $age >= (int) $matches[2]) {
                    $validator->errors()->add('group_id', 'ბავშვის ასაკი არჩეულ ასაკობრივ ჯგუფს არ შეესაბამება.');
                }
            } catch (\Throwable $exception) {
                // The birth-date validator reports malformed dates.
            }
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
    if ($isNew) {
        app(\App\Services\ParentNotificationService::class)->send(
            $kindergartener,
            'application_created',
            'განაცხადი მიღებულია',
            'თქვენი განაცხადი მიღებულია. მიმდინარე სტატუსი: '.$kindergartener->application_status_label
        );
    }

    // ვამუშავებთ პრივილეგიას
    $canManagePriority = auth()->check() && auth()->user()->isUnionAdmin();
    $canSubmitUnapprovedPriority = !auth()->check() && $isNew;
    if (($canManagePriority || $canSubmitUnapprovedPriority) && $request->filled('priority_id')) {
        if ($kindergartener->priority) {
            // უკვე არსებობს => ვაახლებთ
            $kindergartener->priority->fill([
                'priority_id'    => $request->priority_id,
                'has_permission' => $canManagePriority ? ($request->has_permission ?? 0) : 0
            ]);
            $kindergartener->priority->save();
        } else {
            // ახალი პრივილეგია
            $priority = new KindergartnerPriority([
                'priority_id'    => $request->priority_id,
                'has_permission' => $canManagePriority ? ($request->has_permission ?? 0) : 0
            ]);
            $kindergartener->priority()->save($priority);
        }
    } elseif ($canManagePriority) {
        // თუ საერთოდ მოხსნეს პრივილეგია
        if ($kindergartener->priority) {
            $kindergartener->priority()->delete();
        }
    }

    $kindergartener->load('priority');
    $workflow->syncWaitingPriority($kindergartener);

    $auditAfter = \Illuminate\Support\Arr::only($kindergartener->fresh()->getAttributes(), $auditFields);
    $changes = $this->buildAuditChangesFromValues($auditBefore, $auditAfter);
    $priorityAfter = optional($kindergartener->priority)->only(['priority_id', 'has_permission']);
    $changes += $this->buildAuditChangesFromValues(
        is_array($priorityBefore) ? $priorityBefore : [],
        is_array($priorityAfter) ? $priorityAfter : []
    );

    $action = $isNew ? 'kindergartener.create' : 'kindergartener.update';
    $this->logAudit(
        $action,
        Kindergartener::class,
        $kindergartener->id,
        $isNew ? 'აღსაზრდელის ჩანაწერი შეიქმნა' : 'აღსაზრდელის მონაცემები განახლდა',
        $changes
    );

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
        if ($model) DB::transaction(function () use ($model, $workflow, $id, $details) {
            if (!in_array($model->application_status, [ApplicationWorkflowService::CANCELLED, ApplicationWorkflowService::GRADUATED], true)) {
                $model = $workflow->transition($model, ApplicationWorkflowService::CANCELLED, 'Record deleted');
            }
            $model->delete();
            $this->logAudit('kindergartener.delete', Kindergartener::class, $id, 'Kindergartener deleted', $details, true);
        }, 3);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'აღსაზრდელი წაიშალა ბაზიდან!'
        ];
        return redirect()->route('kindergarteners.index')->with($message);
    }


    public function order(Request $request, ApplicationWorkflowService $workflow, SuspensionWorkflowService $suspensions)
    {
       $errs = [];
       if ($request->missing('ids')) { $errs = Arr::prepend($errs, 'მონიშნეთ აღსაზრდელი/არსაზრდელები');};
       if (!$request->filled('action')) { $errs = Arr::prepend($errs, 'მართვის ველი ცარიელია');};
       if (!$request->filled('destination')) { $errs = Arr::prepend($errs, 'ცვლილების ველი ცარიელია');};
       if ((string) $request->action === '2' && mb_strlen(trim((string) $request->reason)) < 5) { $errs = Arr::prepend($errs, 'სტატუსის ცვლილებისთვის მიუთითეთ მიზეზი (მინიმუმ 5 სიმბოლო)');};
       
       if ($errs) return redirect()->route('kindergarteners.index')->withErrors($errs);
       $list = Kindergartener::whereIn('id', $request->ids)
           ->when(auth()->user()->role === 'director', fn ($q) => $q->where('kindergarten_id', auth()->user()->kindergarten_id))->get();
       abort_unless($list->count() === count($request->ids), 403);

       $action = $request->action;
       $destination = $request->destination;

       $reason = trim((string) $request->reason);
       $message = DB::transaction(function () use ($list, $action, $destination, $workflow, $suspensions, $request, $reason) {
       $list->each(function ($item, $key) use($action,$destination,$workflow,$suspensions,$reason) {
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
            if ($item->application_status !== $destination) {
                if ($destination === ApplicationWorkflowService::SUSPENDED) {
                    $item = $suspensions->suspend($item, $reason);
                } else {
                    $item = $workflow->transition($item, $destination, $reason);
                    app(\App\Services\ParentNotificationService::class)->send($item,'status_'.$destination,'განაცხადის სტატუსი შეიცვალა','გაცნობებთ, თქვენი ბავშვის რეგისტრაციის სტატუსი შეიცვალა: '.config('statuses.application.'.$destination, $destination));
                }
            }
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
                     'reason' => $reason ?: null,
                     'ids' => $request->ids
             ], true);
       return $message;
       });

       return redirect()->route('kindergarteners.index')->with($message);
    }

    public function findKid (Request $request) {
         $request->validate(['kids_personal_number' => ['required', 'digits:11'], 'mobile_last_four' => ['required', 'digits:4']]);
         $kid = Kindergartener::with(['kindergarten:id,name', 'groupRange:id,range'])
                ->where('kids_personal_number', $request->kids_personal_number)
                ->where('mobile_number', 'like', '%'.$request->mobile_last_four)
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
        $municipalities = Municipality::query()
            ->select(['id', 'name'])
            ->with([
                'kindergartens' => fn ($query) => $query->select(['id', 'municipality_id', 'name']),
                'kindergartens.groupAgeRanges' => fn ($query) => $query->select(['id', 'range']),
            ])->get();
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
        $basic = (array) data_get(Setting::where('slug', 'basic')->first(), 'object', []);

        return response()->json([
            'priorities' => Priority::query()->get(['id', 'name']),
            'setting' => [
                'object' => [
                    'isRegistrationStart' => (bool) ($basic['isRegistrationStart'] ?? false),
                    'isPrioritetiesStart' => (bool) ($basic['isPrioritetiesStart'] ?? false),
                ],
            ],
            'learning_start_date' => data_get(Setting::where('slug', 'date')->first(), 'object.start'),
            'municipalities' => $municipalities,
        ]);
    }

    public function export() 
    {
        abort_if(auth()->user()->role === 'director' && !auth()->user()->kindergarten_id, 403);
        return Excel::download(new KindergartenerExport(auth()->user()->role === 'director' ? auth()->user()->kindergarten_id : null), 'kindergarteners.xlsx');
    }
}











