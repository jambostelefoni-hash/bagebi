<?php

namespace App\Services;

use App\Model\API\Kindergartener;
use App\Model\ApplicationStatusHistory;
use App\Model\AuditLog;
use App\Model\GroupAgeRange;
use App\Model\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnnualPortingService
{
    public function execute(): array
    {
        abort_unless(auth()->user() && auth()->user()->isUnionAdmin(), 403);

        return DB::transaction(function () {
            $basic = Setting::where('slug', 'basic')->lockForUpdate()->firstOrFail();
            $dates = Setting::where('slug', 'date')->lockForUpdate()->firstOrFail();
            $flags = $basic->object;
            try {
                $end = data_get($dates, 'object.end');
                if (!$end) throw new \InvalidArgumentException();
                $end = Carbon::parse($end)->startOfDay();
            } catch (\Throwable $exception) {
                $this->fail('მიუთითეთ სასწავლო წლის დასრულების სწორი თარიღი.');
            }
            if (empty($flags['canPorting']) || ($flags['isLearningStart'] ?? null) !== false || $end->isFuture()) {
                $this->fail('პორტირება შესაძლებელია მხოლოდ სასწავლო წლის დასრულების შემდეგ.');
            }
            if (!empty($flags['last_ported_end']) && $end->toDateString() <= $flags['last_ported_end']) {
                $this->fail('ამ სასწავლო წლის პორტირება უკვე შესრულებულია.');
            }

            $capacities = DB::table('kindergarten_group_age_range')->orderBy('kindergarten_id')
                ->orderBy('group_age_range')->lockForUpdate()->get();
            $children = Kindergartener::whereIn('application_status', ['registered', 'waiting', 'enrolled', 'suspended'])
                ->orderBy('id')->lockForUpdate()->get();
            $entries = DB::table('waiting_list_entries')->orderBy('id')->lockForUpdate()->get();
            $offers = DB::table('placement_offers')->whereNull('responded_at')->lockForUpdate()->get();
            if ($offers->isNotEmpty() || $entries->contains('state', 'offered') || $capacities->sum('space_reserved') > 0) {
                $this->fail('ჯერ დაასრულეთ მიმდინარე ადგილის შეთავაზებები; შემდეგ გაიმეორეთ პორტირება.');
            }

            $groups = GroupAgeRange::orderBy('id')->get()->keyBy('id');
            $byRange = [];
            foreach ($groups as $group) {
                $range = $this->range($group->range);
                if ($range) $byRange[$range[0].'-'.$range[1]][] = $group->id;
            }
            $slots = $capacities->keyBy(fn ($row) => $row->kindergarten_id.':'.$row->group_age_range);
            $gardens = DB::table('kindergartens')->pluck('name', 'id');
            $plan = [];
            $occupied = [];
            foreach ($children as $child) {
                $group = $groups->get($child->group_id);
                $range = $group ? $this->range($group->range) : null;
                if (!$range || $child->graduate) $this->fail('ჩანაწერი #'.$child->id.': ასაკობრივი ჯგუფი ან დასრულების სტატუსი არასწორია.');
                $target = null;
                if ($range[0] < 5) {
                    $candidates = $byRange[($range[0] + 1).'-'.($range[1] + 1)] ?? [];
                    if (count($candidates) !== 1) $this->fail('ჯგუფისთვის „'.$group->range.'“ მიუთითეთ ერთადერთი მომდევნო ასაკობრივი დიაპაზონი.');
                    $target = $candidates[0];
                    $key = $child->kindergarten_id.':'.$target;
                    if (!$slots->has($key) || (int) $slots[$key]->space_length < 1) {
                        $this->fail(($gardens[$child->kindergarten_id] ?? 'ბაღი').' — ჯგუფს „'.$groups[$target]->range.'“ ჯერ განუსაზღვრეთ ზღვარი.');
                    }
                    if (in_array($child->application_status, ['enrolled', 'suspended'], true)) {
                        $occupied[$key] = ($occupied[$key] ?? 0) + 1;
                    }
                }
                $plan[] = [$child, $target];
            }
            foreach ($occupied as $key => $count) {
                if ($count > (int) $slots[$key]->space_length) {
                    $slot = $slots[$key];
                    $this->fail($gardens[$slot->kindergarten_id].' — ჯგუფი „'.$groups[$slot->group_age_range]->range.'“: საჭიროა '.$count.' ადგილი, ზღვარია '.$slot->space_length.'.');
                }
            }

            $result = ['moved' => 0, 'graduated' => 0];
            foreach ($plan as [$child, $target]) {
                $fromGroup = $child->group_id;
                $fromStatus = $child->application_status;
                $child->group_id = $target;
                if ($target === null) {
                    $child->application_status = 'graduated';
                    $child->active_status_id = 3;
                    $child->graduate = true;
                    $child->status_changed_at = now();
                    $child->suspended_at = null;
                    $result['graduated']++;
                    ApplicationStatusHistory::create(['kindergartener_id' => $child->id, 'from_status' => $fromStatus,
                        'to_status' => 'graduated', 'changed_by' => auth()->id(), 'reason' => 'სასწავლო წლის დასრულება: '.$end->toDateString()]);
                    DB::table('waiting_list_entries')->where('kindergartener_id', $child->id)->update(['state' => 'completed', 'updated_at' => now()]);
                } else {
                    $result['moved']++;
                    DB::table('waiting_list_entries')->where('kindergartener_id', $child->id)
                        ->where('state', 'waiting')->update(['group_id' => $target, 'updated_at' => now()]);
                }
                $child->save();
                $this->audit('kindergartener.ported', Kindergartener::class, $child->id, [
                    'group_id' => ['old' => $fromGroup, 'new' => $target],
                    'application_status' => ['old' => $fromStatus, 'new' => $child->application_status],
                ]);
            }
            foreach ($capacities as $slot) {
                $count = $occupied[$slot->kindergarten_id.':'.$slot->group_age_range] ?? 0;
                DB::table('kindergarten_group_age_range')->where('kindergarten_id', $slot->kindergarten_id)
                    ->where('group_age_range', $slot->group_age_range)->update([
                        'space_filled' => $count, 'space_free' => (int) $slot->space_length - $count,
                    ]);
            }
            $flags['canPorting'] = false;
            $flags['last_ported_end'] = $end->toDateString();
            $basic->object = $flags;
            $basic->save();
            $this->audit('settings.learning', Setting::class, $basic->id, $result + ['school_year_end' => $end->toDateString()]);
            return $result;
        }, 3);
    }

    private function range(string $label): ?array
    {
        if (!preg_match('/^\s*([2-5])\s*[-–—]\s*([3-6])\s*$/u', $label, $match)) return null;
        return (int) $match[2] > (int) $match[1] ? [(int) $match[1], (int) $match[2]] : null;
    }

    private function fail(string $message): void
    {
        throw ValidationException::withMessages(['porting' => $message]);
    }

    private function audit(string $action, string $type, int $id, array $changes): void
    {
        $actor = auth()->user();
        AuditLog::create(['user_id' => $actor->id, 'actor_name' => $actor->name, 'actor_email' => $actor->email,
            'actor_role' => $actor->role, 'action' => $action, 'model_type' => $type, 'model_id' => $id,
            'description' => 'წლიური პორტირება', 'changes' => $changes, 'ip' => request()->ip(), 'user_agent' => request()->userAgent()]);
    }
}
