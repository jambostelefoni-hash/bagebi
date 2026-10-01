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
        $dateSetting = Setting::where('slug', 'date')->firstOrNew();
        $permission = $dateSetting->toArray();
        $permission['object'] = array_merge(['start' => null, 'end' => null], $permission['object'] ?? []);
        $model = Setting::where('slug', 'basic')->firstOrNew();
        $now = Carbon::today();
        $start = $this->parseSettingDate($permission['object']['start']);
        $end = $this->parseSettingDate($permission['object']['end']);
        $isLearningStarted = filter_var(data_get($model->object, 'isLearningStart', false), FILTER_VALIDATE_BOOLEAN);
        $canPorting = filter_var(data_get($model->object, 'canPorting', false), FILTER_VALIDATE_BOOLEAN);
        $isLearningEnded = !$isLearningStarted && $canPorting;
        $canStart = $start ? $now->gte($start) && !$isLearningStarted && !$canPorting : false;
        $canEnd = $end ? $now->gte($end) && $isLearningStarted : false;
        $portingAvailable = $end ? $now->gte($end) && $isLearningEnded : false;

        return view('settings.index', [
            'model' => $model,
            'permission' => $permission,
            'canStart' => $canStart,
            'canEnd' => $canEnd,
            'isLearningStarted' => $isLearningStarted,
            'isLearningEnded' => $isLearningEnded,
            'portingAvailable' => $portingAvailable,
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
        $start = $this->parseSettingDate(data_get($permission, 'object.start'));
        if (!$start) return back()->withErrors(['date' => 'სასწავლო წლის დაწყების თარიღი არასწორია.']);

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
        $end = $this->parseSettingDate(data_get($permission, 'object.end'));
        if (!$end) return back()->withErrors(['date' => 'სასწავლო წლის დასრულების თარიღი არასწორია.']);
        if ($end->isFuture()) return back()->withErrors(['date' => 'სასწავლო წლის დასრულების თარიღი ჯერ არ დამდგარა.']);

        $basic = Setting::where('slug', 'basic')->first();
        $oldBasic = $basic->toArray()['object'];
        if (!empty($oldBasic['last_ported_end']) && $end->toDateString() <= $oldBasic['last_ported_end']) {
            return back()->withErrors(['date' => 'ამ სასწავლო წლის პორტირება უკვე შესრულებულია.']);
        }
        $oldBasic['canPorting'] = true;
        $oldBasic['isLearningStart'] = false;

        $basic->object = $oldBasic;
        $basic->save();

        $this->logAudit('settings.learningEnd', Setting::class, $basic->id, 'Learning end executed');

        return back()->withInput()->withErrors([])->with($message);
    }

    public function learning(Request $request, \App\Services\AnnualPortingService $porting)
    {
        $result = $porting->execute();

        return back()->with([
            'flashType' => 'success',
            'flashMessage' => 'პორტირება დასრულდა: გადაყვანილია '.$result['moved'].'; დასრულებულია '.$result['graduated'].'. ჯგუფების ზღვრები შენარჩუნებულია.',
        ]);
    }

    public function portingPreview(Request $request, \App\Services\AnnualPortingService $porting)
    {
        $preview = $porting->preview();
        $preview['moves_total'] = $preview['moves']->count();
        $preview['graduates_total'] = $preview['graduates']->count();
        $preview['moves'] = $this->paginatePreview($preview['moves'], $request, 'moves_page');
        $preview['graduates'] = $this->paginatePreview($preview['graduates'], $request, 'graduates_page');
        return view('settings.porting-preview', compact('preview'));
    }

    private function paginatePreview($items, Request $request, string $pageName)
    {
        $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage($pageName);
        return new \Illuminate\Pagination\LengthAwarePaginator($items->forPage($page, 25)->values(), $items->count(), 25, $page, ['path' => $request->url(), 'pageName' => $pageName, 'query' => $request->query()]);
    }

    private function parseSettingDate($value): ?Carbon
    {
        if (!$value) return null;

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
