<?php

namespace App\Services;

use App\Model\API\Kindergartener;
use App\Model\ApplicationStatusHistory;
use App\Model\WaitingListEntry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationWorkflowService
{
    const REGISTERED = 'registered';
    const WAITING = 'waiting';
    const ENROLLED = 'enrolled';
    const SUSPENDED = 'suspended';
    const CANCELLED = 'cancelled';
    const GRADUATED = 'graduated';

    public function save(array $input, ?Kindergartener $model = null): Kindergartener
    {
        return DB::transaction(function () use ($input, $model) {
            $model = $model ?: new Kindergartener();
            if ($model->exists) $model = Kindergartener::whereKey($model->id)->lockForUpdate()->firstOrFail();
            $isNew = !$model->exists;
            $oldGarden = $model->kindergarten_id;
            $oldGroup = $model->group_id;
            $oldStatus = $model->application_status;
            $attributes = Arr::only($input, ['kids_personal_number','kids_first_name','kids_last_name','birth_date','mother_personal_number','mother_first_name','mother_last_name','father_personal_number','father_first_name','father_last_name','mobile_number','email','municipality_id','kindergarten_id','group_id']);
            $moved = !$isNew && ((int)$oldGarden !== (int)$attributes['kindergarten_id'] || (int)$oldGroup !== (int)$attributes['group_id']);
            $capacity = ($isNew || $moved) ? $this->lockCapacity($attributes['kindergarten_id'], $attributes['group_id']) : null;

            if ($moved) $this->closeWaitingEntry($model);
            if ($moved && in_array($oldStatus, [self::ENROLLED, self::SUSPENDED], true)) $this->releaseCapacity($oldGarden, $oldGroup);
            $newStatus = $oldStatus ?: self::REGISTERED;
            if ($isNew || $moved) {
                $queueExists = $oldStatus !== self::SUSPENDED && WaitingListEntry::where('kindergarten_id', $attributes['kindergarten_id'])
                    ->where('group_id', $attributes['group_id'])->whereIn('state', ['waiting', 'offered'])->exists();
                $newStatus = !$queueExists && ((int)$capacity->space_free - (int)$capacity->space_reserved) > 0 ? self::ENROLLED : self::WAITING;
            }
            if ($moved && $oldStatus === self::SUSPENDED) {
                if ($newStatus === self::WAITING) throw ValidationException::withMessages(['group_id' => 'შეჩერებული ბავშვის გადასაყვანად თავისუფალი ადგილი საჭიროა.']);
                $newStatus = self::SUSPENDED;
            }

            $model->fill($attributes);
            $model->application_status = $newStatus;
            $model->active_status_id = $this->legacyStatusId($newStatus);
            if ($oldStatus !== $newStatus) $model->status_changed_at = now();
            $model->save();

            if (($isNew || $moved) && in_array($newStatus, [self::ENROLLED, self::SUSPENDED], true)) {
                $this->consumeCapacity($model->kindergarten_id, $model->group_id);
                WaitingListEntry::where('kindergartener_id', $model->id)->update(['state' => 'completed']);
            } elseif ($newStatus === self::WAITING && ($isNew || $moved || !WaitingListEntry::where('kindergartener_id', $model->id)->exists())) {
                WaitingListEntry::updateOrCreate(['kindergartener_id' => $model->id], [
                    'kindergarten_id' => $model->kindergarten_id,
                    'group_id' => $model->group_id,
                    'priority_rank' => !empty($input['priority_id']) && !empty($input['has_permission']) ? max(1, (int)$input['priority_id']) : 999,
                    'queued_at' => now(), 'state' => 'waiting',
                ]);
            }
            if ($oldStatus !== $newStatus) $this->recordHistory($model, $oldStatus, $newStatus, $isNew ? 'Application created' : 'Placement changed');
            return $model->fresh();
        }, 3);
    }

    public function transition(Kindergartener $child, string $status, ?string $reason = null): Kindergartener
    {
        $allowed = [self::REGISTERED,self::WAITING,self::ENROLLED,self::SUSPENDED,self::CANCELLED,self::GRADUATED];
        if (!in_array($status, $allowed, true)) throw ValidationException::withMessages(['status' => 'Invalid application status.']);

        return DB::transaction(function () use ($child, $status, $reason) {
            $child = Kindergartener::whereKey($child->id)->lockForUpdate()->firstOrFail();
            $from = $child->application_status;
            if ($from === $status) return $child;
            if ($from === self::GRADUATED) {
                throw ValidationException::withMessages(['status' => 'დამთავრებული ბავშვის საბოლოო სტატუსის შეცვლა შეუძლებელია.']);
            }
            if ($status === self::SUSPENDED && $from !== self::ENROLLED) {
                throw ValidationException::withMessages(['status' => 'შეჩერება შესაძლებელია მხოლოდ ჩარიცხული ბავშვისთვის.']);
            }
            $this->closeWaitingEntry($child);
            if (in_array($from,[self::ENROLLED,self::SUSPENDED],true) && !in_array($status,[self::ENROLLED,self::SUSPENDED],true)) $this->releaseCapacity($child->kindergarten_id, $child->group_id);
            if (!in_array($from,[self::ENROLLED,self::SUSPENDED],true) && $status === self::ENROLLED) {
                $capacity = $this->lockCapacity($child->kindergarten_id, $child->group_id);
                if (((int)$capacity->space_free - (int)$capacity->space_reserved) < 1) throw ValidationException::withMessages(['status' => 'No free capacity is available.']);
                $this->consumeCapacity($child->kindergarten_id, $child->group_id);
            }
            $child->application_status = $status;
            $child->active_status_id = $this->legacyStatusId($status);
            $child->status_changed_at = now();
            $child->suspended_at = $status === self::SUSPENDED ? now() : null;
            $child->graduate = $status === self::GRADUATED;
            if ($status === self::GRADUATED) $child->group_id = null;
            $child->save();
            $this->recordHistory($child, $from, $status, $reason);
            if ($status === self::WAITING) WaitingListEntry::updateOrCreate(['kindergartener_id' => $child->id], ['kindergarten_id' => $child->kindergarten_id,'group_id' => $child->group_id,'queued_at' => now(),'state' => 'waiting']);
            return $child->fresh();
        }, 3);
    }

    public function syncWaitingPriority(Kindergartener $child): void
    {
        $priority = $child->priority;
        WaitingListEntry::where('kindergartener_id', $child->id)
            ->whereIn('state', ['waiting', 'offered'])
            ->update([
                'priority_rank' => $priority && $priority->has_permission
                    ? max(1, (int) $priority->priority_id)
                    : 999,
            ]);
    }

    public function acceptReservedPlacement(Kindergartener $child, WaitingListEntry $entry): Kindergartener
    {
        return DB::transaction(function () use ($child, $entry) {
            $child = Kindergartener::whereKey($child->id)->lockForUpdate()->firstOrFail();
            $entry = WaitingListEntry::whereKey($entry->id)->lockForUpdate()->firstOrFail();
            if ($child->application_status !== self::WAITING || $entry->state !== 'offered' || (int)$entry->kindergartener_id !== (int)$child->id || (int)$entry->kindergarten_id !== (int)$child->kindergarten_id || (int)$entry->group_id !== (int)$child->group_id) {
                throw ValidationException::withMessages(['offer' => 'შეთავაზება აღარ არის აქტიური.']);
            }
            $capacity = $this->lockCapacity($child->kindergarten_id, $child->group_id);
            if ((int)$capacity->space_reserved < 1) throw ValidationException::withMessages(['offer' => 'Reserved placement is no longer available.']);
            DB::table('kindergarten_group_age_range')->where('kindergarten_id',$child->kindergarten_id)->where('group_age_range',$child->group_id)->update(['space_reserved'=>DB::raw('space_reserved - 1'),'space_filled'=>DB::raw('CAST(space_filled AS UNSIGNED) + 1'),'space_free'=>DB::raw('CAST(space_free AS UNSIGNED) - 1')]);
            $from = $child->application_status;
            $child->update(['application_status'=>self::ENROLLED,'active_status_id'=>$this->legacyStatusId(self::ENROLLED),'status_changed_at'=>now()]);
            $entry->update(['state'=>'completed']);
            $this->recordHistory($child, $from, self::ENROLLED, 'Placement offer accepted');
            return $child->fresh();
        }, 3);
    }

    private function closeWaitingEntry(Kindergartener $child): void
    {
        $entry = WaitingListEntry::where('kindergartener_id', $child->id)->lockForUpdate()->first();
        if (!$entry) return;
        if ($entry->state === 'offered') {
            DB::table('kindergarten_group_age_range')->where('kindergarten_id', $entry->kindergarten_id)->where('group_age_range', $entry->group_id)->where('space_reserved', '>', 0)->decrement('space_reserved');
        }
        \App\Model\PlacementOffer::where('waiting_list_entry_id', $entry->id)->whereNull('responded_at')->update(['responded_at' => now(), 'response' => 'expired']);
        $entry->update(['state' => 'completed']);
    }

    private function recordHistory($child, $from, $to, $reason)
    {
        ApplicationStatusHistory::create(['kindergartener_id'=>$child->id,'from_status'=>$from,'to_status'=>$to,'changed_by'=>auth()->id(),'reason'=>$reason]);
    }

    private function lockCapacity($garden, $group)
    {
        $row = DB::table('kindergarten_group_age_range')->where('kindergarten_id',$garden)->where('group_age_range',$group)->lockForUpdate()->first();
        if (!$row) throw ValidationException::withMessages(['group_id' => 'არჩეული ჯგუფი ამ ბაღს არ ეკუთვნის.']);
        return $row;
    }

    private function consumeCapacity($garden, $group)
    {
        $changed = DB::table('kindergarten_group_age_range')->where('kindergarten_id',$garden)->where('group_age_range',$group)->whereRaw('CAST(space_free AS SIGNED) - space_reserved > 0')->update(['space_filled'=>DB::raw('CAST(space_filled AS UNSIGNED) + 1'),'space_free'=>DB::raw('CAST(space_free AS UNSIGNED) - 1')]);
        if (!$changed) throw ValidationException::withMessages(['group_id' => 'თავისუფალი ადგილი აღარ არის.']);
    }

    private function releaseCapacity($garden, $group)
    {
        if (!$garden || !$group) return;
        DB::table('kindergarten_group_age_range')->where('kindergarten_id',$garden)->where('group_age_range',$group)->lockForUpdate()->update(['space_filled'=>DB::raw('GREATEST(CAST(space_filled AS UNSIGNED) - 1, 0)'),'space_free'=>DB::raw('LEAST(CAST(space_free AS UNSIGNED) + 1, CAST(space_length AS UNSIGNED))')]);
    }

    private function legacyStatusId(string $status): int
    {
        return [self::WAITING=>1,self::REGISTERED=>1,self::ENROLLED=>2,self::GRADUATED=>3,self::SUSPENDED=>4,self::CANCELLED=>4][$status] ?? 1;
    }
}
