<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



use App\User;
use App\Model\Setting;
use App\Model\Municipality;
use App\Model\Kindergarten;
use App\Model\API\Kindergartener;
use App\Model\Attendance;
use App\Model\PlacementOffer;
use App\Model\ReinstatementRequest;
use App\Model\GroupAgeRange;
use App\Model\DataQualityIssue;
use App\Model\NotificationDelivery;
use App\Services\WorkCalendarService;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        $children = Kindergartener::query()->when($user->role === 'director', fn ($q) => $q->where('kindergarten_id', $user->kindergarten_id));
        $user_count = $user->isUnionAdmin() ? User::count() : 1;
        $municipality_count = $user->isUnionAdmin() ? Municipality::count() : 1;
        $kindergarten_count = $user->isUnionAdmin() ? Kindergarten::count() : 1;
        $kindergartner_count = (clone $children)->count();
        $enrolled_count = (clone $children)->where('application_status', 'enrolled')->count();
        $waiting_count = (clone $children)->where('application_status', 'waiting')->count();
        $suspended_count = (clone $children)->where('application_status', 'suspended')->count();
        
        $date = Setting::where('slug', 'date')->firstOrNew()->toArray();
        $basic = Setting::where('slug', 'basic')->firstOrNew()->toArray();

        $adminOverview = null;
        if ($user->isUnionAdmin()) {
            $adminOverview = [
                'pending_documents' => ReinstatementRequest::whereIn('status', ['pending', 'needs_correction'])->whereNotNull('document_path')->count(),
                'active_offers' => PlacementOffer::whereNull('responded_at')->where('expires_at', '>', now())->count(),
                'open_quality_issues' => DataQualityIssue::where('status', 'open')->count(),
                'failed_sms' => NotificationDelivery::where('status', 'failed')->count(),
            ];
        }

        $directorTasks = null;
        if (!$user->isUnionAdmin() && $user->kindergarten_id) {
            $today = now()->toDateString();
            $workingDay = app(WorkCalendarService::class)->isWorkingDay(now()->startOfDay(), $user->kindergarten_id);
            $groups = GroupAgeRange::pluck('range', 'id');
            $expected = Kindergartener::where('kindergarten_id', $user->kindergarten_id)
                ->where('application_status', 'enrolled')->select('group_id', \DB::raw('COUNT(*) as total'))
                ->groupBy('group_id')->pluck('total', 'group_id');
            $recorded = Attendance::where('kindergarten_id', $user->kindergarten_id)->whereDate('attendance_date', $today)
                ->select('group_id', \DB::raw('COUNT(*) as total'))->groupBy('group_id')->pluck('total', 'group_id');
            $attendanceRisks = $this->attendanceRisks($user->kindergarten_id);

            $directorTasks = [
                'working_day' => $workingDay,
                'absent_count' => $workingDay ? Attendance::where('kindergarten_id', $user->kindergarten_id)->whereDate('attendance_date', $today)
                    ->where('status', Attendance::ABSENT)->count() : 0,
                'absent' => $workingDay ? Attendance::with('kindergartener.groupRange')
                    ->where('kindergarten_id', $user->kindergarten_id)->whereDate('attendance_date', $today)
                    ->where('status', Attendance::ABSENT)->latest()->limit(8)->get() : collect(),
                'unfilled_groups' => $workingDay ? $expected->filter(fn ($total, $groupId) => (int) ($recorded[$groupId] ?? 0) < $total)
                    ->map(fn ($total, $groupId) => (object) ['id' => $groupId, 'range' => $groups[$groupId] ?? 'უცნობი ჯგუფი', 'recorded' => (int) ($recorded[$groupId] ?? 0), 'total' => (int) $total])->values() : collect(),
                'pending_documents' => ReinstatementRequest::whereIn('status', ['pending', 'needs_correction'])->whereNotNull('document_path')
                    ->whereHas('kindergartener', fn ($query) => $query->where('kindergarten_id', $user->kindergarten_id))->count(),
                'offers' => PlacementOffer::with('entry.kindergartener.groupRange')
                    ->whereNull('responded_at')->where('expires_at', '>', now())
                    ->whereHas('entry', fn ($query) => $query->where('kindergarten_id', $user->kindergarten_id)->where('state', 'offered'))
                    ->orderBy('expires_at')->limit(5)->get(),
                'attendance_risks' => $attendanceRisks,
            ];
        }

        return view('home', [
            'user_count' => $user_count,
            'municipality_count' => $municipality_count,
            'kindergarten_count' => $kindergarten_count,
            'kindergartner_count' => $kindergartner_count,
            'enrolled_count' => $enrolled_count,
            'waiting_count' => $waiting_count,
            'suspended_count' => $suspended_count,
            'date' => $date,
            'basic' => $basic,
            'adminOverview' => $adminOverview,
            'directorTasks' => $directorTasks,
        ]);
    }

    private function attendanceRisks(int $kindergartenId)
    {
        $calendar = app(WorkCalendarService::class);
        $from = now()->startOfMonth()->min(today()->copy()->subDays(45))->toDateString();

        return Kindergartener::with(['groupRange', 'attendances' => fn ($query) => $query
            ->whereDate('attendance_date', '>=', $from)
            ->whereDate('attendance_date', '<=', today())])
            ->where('kindergarten_id', $kindergartenId)
            ->where('application_status', 'enrolled')
            ->get()
            ->map(function ($child) use ($calendar, $kindergartenId) {
                $records = $child->attendances->keyBy(fn ($attendance) => $attendance->attendance_date->toDateString());
                $monthly = $child->attendances->filter(fn ($attendance) =>
                    $attendance->status === Attendance::ABSENT
                    && $attendance->attendance_date->isSameMonth(today())
                    && $calendar->isWorkingDay($attendance->attendance_date, $kindergartenId)
                )->count();

                $cursor = today();
                $streak = 0;
                for ($days = 0; $days < 45; $days++, $cursor->subDay()) {
                    if (!$calendar->isWorkingDay($cursor, $kindergartenId)) continue;
                    $record = $records->get($cursor->toDateString());
                    if (!$record && $cursor->isToday()) continue;
                    if (!$record || $record->status !== Attendance::ABSENT) break;
                    $streak++;
                }

                if ($streak < 7 && $monthly < 12) return null;

                $remainingConsecutive = max(0, 10 - $streak);
                $remainingMonthly = max(0, 15 - $monthly);
                $remaining = min($remainingConsecutive, $remainingMonthly);

                return (object) [
                    'child' => $child,
                    'streak' => $streak,
                    'monthly' => $monthly,
                    'remaining' => $remaining,
                    'level' => $remaining <= 1 ? 'critical' : 'warning',
                ];
            })
            ->filter()
            ->sortBy('remaining')
            ->values();
    }
}




