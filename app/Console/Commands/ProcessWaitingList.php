<?php

namespace App\Console\Commands;

use App\Model\AuditLog;
use App\Model\PlacementOffer;
use App\Model\Setting;
use App\Model\WaitingListEntry;
use App\Services\ApplicationWorkflowService;
use App\Services\ParentNotificationService;
use App\Services\SystemProcessMonitor;
use App\Services\WorkCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessWaitingList extends Command
{
    protected $signature = 'waiting-list:process';
    protected $description = 'Expire offers and reserve available places for the waiting list';

    public function handle(WorkCalendarService $calendar, ParentNotificationService $notifications, SystemProcessMonitor $monitor, ApplicationWorkflowService $workflow)
    {
        return $monitor->record('waiting_list', function () use ($calendar, $notifications, $workflow) {
            PlacementOffer::whereNull('responded_at')->where('expires_at', '<', now())->each(function (PlacementOffer $candidate) use ($notifications, $workflow) {
                DB::transaction(function () use ($candidate, $notifications, $workflow) {
                    $offer = PlacementOffer::with('entry.kindergartener')->whereKey($candidate->id)->lockForUpdate()->first();
                    if (!$offer || $offer->responded_at || !$offer->entry || $offer->entry->state !== 'offered') return;

                    $offer->update(['responded_at' => now(), 'response' => 'expired']);
                    $child = $workflow->transition($offer->entry->kindergartener, ApplicationWorkflowService::CANCELLED, 'Placement offer expired');
                    $notifications->send($child, 'placement_offer_expired', 'ადგილის შეთავაზების ვადა ამოიწურა', 'სამწუხაროდ, ადგილის შეთავაზების ვადა ამოიწურა. საჭიროების შემთხვევაში შეგიძლიათ ახალი რეგისტრაციის შესახებ დაუკავშირდეთ ადმინისტრაციას.');
                    $this->audit('waiting_list.offer_expired', PlacementOffer::class, $offer->id, ['waiting_list_entry_id' => $offer->waiting_list_entry_id]);
                }, 3);
            });

            $days = (int) data_get(Setting::where('slug', 'basic')->first(), 'object.offer_working_days', 3);
            $capacities = DB::table('kindergarten_group_age_range')->whereRaw('CAST(space_free AS SIGNED) - space_reserved > 0')->get();
            foreach ($capacities as $capacity) {
                $available = (int) $capacity->space_free - (int) $capacity->space_reserved;
                for ($i = 0; $i < $available; $i++) {
                    $created = DB::transaction(function () use ($capacity, $days, $calendar, $notifications) {
                        $current = DB::table('kindergarten_group_age_range')->where('kindergarten_id', $capacity->kindergarten_id)->where('group_age_range', $capacity->group_age_range)->lockForUpdate()->first();
                        if (!$current || (int) $current->space_free - (int) $current->space_reserved < 1) return false;

                        $entry = WaitingListEntry::where('kindergarten_id', $capacity->kindergarten_id)
                            ->where('group_id', $capacity->group_age_range)->where('state', 'waiting')
                            ->whereHas('kindergartener', fn ($query) => $query->where('application_status', 'waiting'))
                            ->orderBy('priority_rank')->orderBy('queued_at')->orderBy('id')->lockForUpdate()->first();
                        if (!$entry) return false;

                        DB::table('kindergarten_group_age_range')->where('kindergarten_id', $capacity->kindergarten_id)->where('group_age_range', $capacity->group_age_range)->increment('space_reserved');
                        $entry->update(['state' => 'offered']);
                        $raw = Str::random(64);
                        $offer = PlacementOffer::create([
                            'waiting_list_entry_id' => $entry->id,
                            'token_hash' => hash('sha256', $raw),
                            'expires_at' => $calendar->addWorkingDays(now(), $days, $entry->kindergarten_id),
                        ]);
                        $notifications->send($entry->kindergartener, 'placement_offer', 'თავისუფალი ადგილი გამოჩნდა', 'გთხოვთ, დაადასტუროთ ან უარყოთ შეთავაზება მითითებულ ვადაში.', route('placement-offers.show', $raw));
                        $this->audit('waiting_list.offer_created', PlacementOffer::class, $offer->id, ['waiting_list_entry_id' => $entry->id, 'kindergarten_id' => $entry->kindergarten_id, 'group_id' => $entry->group_id]);
                        return true;
                    }, 3);
                    if (!$created) break;
                }
            }

            return 0;
        });
    }

    private function audit($action, $model, $id, array $changes): void
    {
        AuditLog::create(['action' => $action, 'model_type' => $model, 'model_id' => $id, 'description' => 'Automated waiting list operation', 'changes' => $changes, 'ip' => null, 'user_agent' => 'scheduler']);
    }
}
