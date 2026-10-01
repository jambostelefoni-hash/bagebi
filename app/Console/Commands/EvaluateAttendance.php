<?php

namespace App\Console\Commands;

use App\Model\API\Kindergartener;
use App\Model\Attendance;
use App\Model\ReinstatementRequest;
use App\Services\ApplicationWorkflowService;
use App\Services\ParentNotificationService;
use App\Services\SuspensionWorkflowService;
use App\Services\SystemProcessMonitor;
use App\Services\WorkCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EvaluateAttendance extends Command
{
    protected $signature = 'attendance:evaluate {--kindergarten=}';
    protected $description = 'Suspend registrations that exceed absence limits';

    public function handle(ApplicationWorkflowService $workflow, WorkCalendarService $calendar, ParentNotificationService $notifications, SystemProcessMonitor $monitor, SuspensionWorkflowService $suspensions)
    {
        return $monitor->record('attendance_evaluation', function () use ($workflow, $calendar, $notifications, $suspensions) {
            $kindergartenId = $this->option('kindergarten');
            Kindergartener::where('application_status', 'enrolled')
                ->when($kindergartenId, fn ($query) => $query->where('kindergarten_id', $kindergartenId))
                ->chunkById(100, function ($children) use ($calendar, $suspensions) {
                    foreach ($children as $candidate) {
                        DB::transaction(function () use ($candidate, $calendar, $suspensions) {
                            $child = Kindergartener::whereKey($candidate->id)->lockForUpdate()->firstOrFail();
                            if ($child->application_status !== 'enrolled') return;

                            $monthCount = Attendance::where('kindergartener_id', $child->id)->where('status', 'absent')
                                ->whereBetween('attendance_date', [now()->startOfMonth()->toDateString(), today()->toDateString()])
                                ->get()->filter(fn ($attendance) => $calendar->isWorkingDay($attendance->attendance_date, $child->kindergarten_id))->count();
                            $recent = Attendance::where('kindergartener_id', $child->id)->where('attendance_date', '<=', today())
                                ->orderByDesc('attendance_date')->limit(31)->get()->keyBy(fn ($attendance) => $attendance->attendance_date->toDateString());
                            $cursor = today();
                            $streak = 0;
                            $first = null;
                            for ($i = 0; $i < 45 && $streak < 10; $i++, $cursor->subDay()) {
                                if (!$calendar->isWorkingDay($cursor, $child->kindergarten_id)) continue;
                                $record = $recent->get($cursor->toDateString());
                                if (!$record || $record->status !== 'absent') break;
                                $streak++;
                                $first = $cursor->copy();
                            }

                            if ($streak >= 10 || $monthCount >= 15) {
                                $suspensions->suspend(
                                    $child,
                                    $streak >= 10 ? '10 consecutive working-day absences' : '15 monthly working-day absences',
                                    $monthCount >= 15 ? now()->startOfMonth() : $first,
                                    today()
                                );
                            }
                        }, 3);
                    }
                });

            ReinstatementRequest::whereIn('status', ['pending', 'needs_correction'])->whereNull('document_path')->where('expires_at', '<', now())
                ->when($kindergartenId, fn ($query) => $query->whereHas('kindergartener', fn ($children) => $children->where('kindergarten_id', $kindergartenId)))
                ->each(function ($candidate) use ($workflow, $notifications) {
                    DB::transaction(function () use ($candidate, $workflow, $notifications) {
                        $request = ReinstatementRequest::whereKey($candidate->id)->lockForUpdate()->first();
                        if (!$request || !in_array($request->status, ['pending', 'needs_correction'], true) || $request->document_path || !$request->expires_at->isPast()) return;
                        $child = Kindergartener::whereKey($request->kindergartener_id)->lockForUpdate()->first();
                        $request->update(['status' => 'expired']);
                        if ($child && $child->application_status === 'suspended') {
                            $child = $workflow->transition($child, 'cancelled', 'Reinstatement deadline expired');
                            $notifications->send($child, 'status_cancelled', 'რეგისტრაცია გაუქმებულია', 'დოკუმენტის წარდგენის ვადა ამოიწურა. ბავშვის რეგისტრაცია გაუქმებულია.');
                        }
                    }, 3);
                });

            return 0;
        });
    }
}
