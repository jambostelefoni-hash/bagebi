<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

use App\Model\Setting;
use App\Model\API\Kindergartener;
use App\Model\Kindergarten;

class SettingController extends Controller
{
    public function index()
    {
        $permission = Setting::where('slug', 'date')->first()->toArray();
        $now = Carbon::createFromFormat('m/d/Y', Carbon::now()->format('m/d/Y'));
        $start = Carbon::createFromFormat('m/d/Y', $permission['object']['start']);
        $end = Carbon::createFromFormat('m/d/Y', $permission['object']['end']);
        $canStart = $now->gte($start);
        $canEnd = $now->gte($end);

        $model = Setting::where('slug', 'basic')->firstOrNew();
        return view('settings.index', [
            'model' => $model,
            'permission' => $permission,
            'canStart' => $canStart,
            'canEnd' => $canEnd
        ]);
    }

    public function store(Request $request)
    {
        $model = Setting::firstOrNew(['slug' => 'basic']);
        $oldData = $model->toArray()['object'];
        $mergeData = array_merge(
            array_merge($oldData, ['isRegistrationStart' => false, 'isPrioritetiesStart' => false]),
            $request->object
        );
        $request->merge(['object' => $mergeData]);
        $model->fill($request->all());
        $model->save();

        $this->logAudit('settings.update', Setting::class, $model->id, 'Basic settings updated', $request->input('object', []));
        
        $message = [
            'flashType'    => 'success',
            'flashMessage' => 'პარამეტრები დაემატა წარმატებით'
        ];

        return back()->withInput()->withErrors([])->with($message);
    }

    public function show($id) {}
    public function update(Request $request, $id) {}
    public function destroy($id) {}

    public function date()
    {
        $model = Setting::where('slug', 'date')->firstOrNew();
        return view('settings.date', ['model' => $model]);
    }

    public function dateStore(Request $request)
    {
        $setting_date = Setting::firstOrNew(['slug' => 'date']);
        $setting_basic = Setting::where(['slug' => 'basic'])->first();

        $validator = Validator::make($request->all(), [
            'object.start' => ['required','date'],
            'object.end' => ['required','date']
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        };

        $setting_date->fill($request->all());
        $setting_date->save();

        $this->logAudit('settings.date', Setting::class, $setting_date->id, 'Date settings updated', $request->input('object', []));

        if(!$setting_basic) {
            $setting_basic = new Setting;
            $setting_basic->fill(
                ['slug' => 'basic', 'object' => ['canPorting' => false, 'isLearningStart' => 'undefined']]
            );
            $setting_basic->save();
        };
        
        $message = [
            'flashType'    => 'success',
            'flashMessage' => 'პარამეტრები დაემატა წარმატებით'
        ];

        return back()->withInput()->withErrors([])->with($message);
    }

    public function learningStart(Request $request)
    {
        $basic = Setting::where('slug', 'basic')->first();
        if ($basic->object['canPorting']) {
            $message = [
                'flashType'    => 'success',
                'flashMessage' => 'სწავლის დაწყებამდე დააჭირეთ პორტირების ღილაკს!'
            ];

            return back()->withInput()->withErrors([])->with($message);
        };

        $kindergartners_has_not_permission = Kindergartener::whereHas('priority', function (Builder $query) {
            $query->where('has_permission', 0);
        });

        $kindergartners_has_not_permission->get()->each(function ($item) {
            $item->active_status_id = 4;
            $item->save();

            // ✅ safe null check with nullsafe operator
            $gardenByGroupAge = $item->kindergarten?->currentAge($item->group_id);

            if ($gardenByGroupAge) {
                $newData = [
                    'space_filled' => $gardenByGroupAge->pivot->space_filled > 0
                        ? $gardenByGroupAge->pivot->space_filled - 1
                        : 0,
                    'space_free' => $gardenByGroupAge->pivot->space_free + 1
                ];

                // ✅ use model instance instead of relation builder
                $item->kindergarten
                    ->groupAgeRanges()
                    ->updateExistingPivot($item->group_id, $newData);
            } else {
                \Log::warning("No age range found for group {$item->group_id} in kindergarten {$item->kindergarten->id}");
            }
        });

        Kindergartener::where('active_status_id', 1)->update(['active_status_id' => 2]);

        $message = [
            'flashType'    => 'success',
            'flashMessage' => 'პარამეტრები დაემატა წარმატებით'
        ];

        $permission = Setting::where('slug', 'date')->first();
        $start = Carbon::createFromFormat('m/d/Y', $permission['object']['start'])->addYear();
        
        $oldData = $permission->toArray()['object'];
        $newData = ['start' => $start->format('m/d/Y')];
        $mergeData = array_merge($oldData, $newData);

        $permission->object = $mergeData;
        $permission->save();

        $oldBasic = $basic->toArray()['object'];
        $oldBasic['canPorting'] = false;
        $oldBasic['isLearningStart'] = true;

        $basic->object = $oldBasic;
        $basic->save();

        $this->logAudit('settings.learningStart', Setting::class, $basic->id, 'Learning start executed');

        return back()->withInput()->withErrors([])->with($message);
    }

    public function learningEnd(Request $request)
    {
        $message = [
            'flashType'    => 'success',
            'flashMessage' => 'პარამეტრები დაემატა წარმატებით'
        ];

        $permission = Setting::where('slug', 'date')->first();
        $end = Carbon::createFromFormat('m/d/Y', $permission['object']['end'])->addYear();
        
        $oldData = $permission->toArray()['object'];
        $newData = ['end' => $end->format('m/d/Y')];
        $mergeData = array_merge($oldData, $newData);

        $permission->object = $mergeData;
        $permission->save();

        $basic = Setting::where('slug', 'basic')->first();
        $oldBasic = $basic->toArray()['object'];
        $oldBasic['canPorting'] = true;
        $oldBasic['isLearningStart'] = false;

        $basic->object = $oldBasic;
        $basic->save();

        $this->logAudit('settings.learningEnd', Setting::class, $basic->id, 'Learning end executed');

        return back()->withInput()->withErrors([])->with($message);
    }

    public function learning(Request $request)
    {
        Kindergartener::all()->each(function($item) {
            if ($item->group_id == 4) {
                $item->active_status_id = 3;
                $item->graduate = 1;
                $item->group_id = NULL;
            } else if (!$item->graduate) {
                $item->group_id = $item->group_id + 1;
            }
            $item->save();
        });

        Kindergarten::all()->each(function($item) {
            $item->groupAgeRanges->each(function($item_range) use ($item) {
                $kindergartenersByGroupId = $item->KindergartenersByGroupId($item_range->id);
                if ($kindergartenersByGroupId) {
                    if (!$kindergartenersByGroupId->total) $kindergartenersByGroupId->total = 0;
                    
                    $newData = [
                        'space_length' => $kindergartenersByGroupId->total,
                        'space_filled' => $kindergartenersByGroupId->total,
                        'space_free' => 0
                    ];

                    $item->groupAgeRanges()->updateExistingPivot($kindergartenersByGroupId->group_id, $newData);
                } else {
                    $item->groupAgeRanges()->updateExistingPivot($item_range->id, [
                        'space_length' => 0,
                        'space_filled' => 0,
                        'space_free' => 0
                    ]);
                }
                $item->save();
            });
            
            $item->groupAgeRanges()->updateExistingPivot(1, ['space_length' => 0, 'space_filled' => 0, 'space_free' => 0]);
            $item->save();
        });

        $message = [
            'flashType'    => 'success',
            'flashMessage' => 'მოსწავლეების ჯგუფიდან ჯგუფში გადაყვანა წარმატებით შესრულდა. აუცილებელია, რომ ეს მოქმედება აღარ შესრულდეს შემდეგი სასწავლო წლის დასრულებამდე!'
        ];

        $basic = Setting::where('slug', 'basic')->first();
        $oldBasic = $basic->toArray()['object'];
        $oldBasic['canPorting'] = false;

        $basic->object = $oldBasic;
        $basic->save();

        $this->logAudit('settings.learning', Setting::class, $basic->id, 'Learning process executed');

        return back()->withInput()->withErrors([])->with($message);
    }
}
