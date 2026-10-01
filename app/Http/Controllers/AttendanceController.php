<?php

namespace App\Http\Controllers;

use App\Model\API\Kindergartener;
use App\Model\Attendance;
use App\Model\GroupAgeRange;
use App\Model\Kindergarten;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;
use App\Services\WorkCalendarService;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $gardenId = $this->gardenId($request, false);
        if (!$gardenId) return view('attendance.empty');
        $date = $request->input('date', now()->toDateString());
        $request->validate(['date' => ['nullable', 'date', 'before_or_equal:today']]);
        $isWorkingDay = app(WorkCalendarService::class)->isWorkingDay(Carbon::parse($date), $gardenId);
        $groupId = $request->input('group_id');
        $children = Kindergartener::where('kindergarten_id', $gardenId)
            ->where(function ($query) use ($date) {
                $query->where('application_status', 'enrolled');

                // გაერთიანების ადმინისტრატორს შეუძლია ისტორიული ჩანაწერის შესწორებაც.
                // დირექტორის ფორმაში კი არ უნდა მოხვდეს შეჩერებული/გაუქმებული ბავშვი.
                if (auth()->user()->isUnionAdmin()) {
                    $query->orWhereHas('attendances', fn ($attendance) => $attendance->whereDate('attendance_date', $date));
                }
            })
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->with(['attendances' => fn ($q) => $q->whereDate('attendance_date', $date)])
            ->orderBy('kids_last_name')->get();

        return view('attendance.index', [
            'children' => $children, 'date' => $date, 'groupId' => $groupId,
            'groups' => GroupAgeRange::pluck('range', 'id'),
            'gardens' => auth()->user()->isUnionAdmin() ? Kindergarten::pluck('name', 'id') : collect(),
            'gardenId' => $gardenId, 'isWorkingDay' => $isWorkingDay,
            'attendanceRisks' => $this->attendanceRisks($children, $gardenId, Carbon::parse($date)),
        ]);
    }

    public function store(Request $request, WorkCalendarService $calendar)
    {
        $gardenId = $this->gardenId($request);
        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'records' => ['required', 'array'],
            'records.*' => ['required', Rule::in(['present','absent','excused','non_working'])],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:500'],
        ]);

        $isWorkingDay = $calendar->isWorkingDay(Carbon::parse($data['date']), $gardenId);
        foreach ($data['records'] as $status) {
            if ($isWorkingDay && $status === Attendance::NON_WORKING) {
                return back()->withErrors(['records' => 'სამუშაო დღისთვის „არასამუშაო დღე“ არ გამოიყენოთ. შეცვალეთ სამუშაო კალენდარი საჭიროების შემთხვევაში.']);
            }
            if (!$isWorkingDay && $status !== Attendance::NON_WORKING) {
                return back()->withErrors(['records' => 'არასამუშაო დღისთვის მხოლოდ „არასამუშაო დღე“ შეიძლება ჩაიწეროს.']);
            }
        }

        DB::transaction(function () use ($data, $gardenId) {
            $children = Kindergartener::where('kindergarten_id', $gardenId)->whereIn('id', array_keys($data['records']))->lockForUpdate()->get()->keyBy('id');
            abort_unless($children->count() === count($data['records']), 403);
            foreach ($data['records'] as $id => $status) {
                $child = $children->get($id);
                $previous = Attendance::where('kindergartener_id', $child->id)->whereDate('attendance_date', $data['date'])->lockForUpdate()->first();
                if ($child->application_status !== 'enrolled' && !(auth()->user()->isUnionAdmin() && $previous)) {
                    throw ValidationException::withMessages([
                        'records' => 'დასწრება ვერ შეინახა: ერთ-ერთი აღსაზრდელი აღარ არის ჩარიცხული. განაახლეთ გვერდი და სცადეთ ხელახლა.',
                    ]);
                }
                $oldStatus = $previous ? $previous->status : null;
                $oldNote = $previous ? $previous->note : null;
                $note = trim((string) data_get($data, 'notes.'.$child->id));
                $note = $note !== '' ? $note : null;
                $attendance = $previous ?: new Attendance([
                    'kindergartener_id' => $child->id,
                    'attendance_date' => $data['date'],
                    'kindergarten_id' => $gardenId,
                    'group_id' => $child->group_id,
                ]);
                $attendance->fill(['status' => $status, 'note' => $note, 'recorded_by' => auth()->id()])->save();
                if ($oldStatus !== $status || $oldNote !== $note) {
                    $changes = ['kindergartener_id' => $child->id, 'date' => $data['date'], 'kindergarten_id' => $gardenId];
                    if ($oldStatus !== $status) $changes['status'] = ['old' => $oldStatus, 'new' => $status];
                    if ($oldNote !== $note) $changes['note'] = ['old' => $oldNote, 'new' => $note];
                    \App\Model\AuditLog::create([
                        'user_id' => auth()->id(), 'actor_name' => auth()->user()->name,
                        'actor_email' => auth()->user()->email, 'actor_role' => auth()->user()->role,
                        'action' => 'attendance.store', 'model_type' => Attendance::class, 'model_id' => $attendance->id,
                        'description' => 'Attendance recorded',
                        'changes' => $changes,
                        'ip' => request()->ip(), 'user_agent' => request()->userAgent(),
                    ]);
                }
            }
        });
        return back()->with(['flashType' => 'success', 'flashMessage' => 'დასწრება წარმატებით შეინახა.']);
    }

    public function export(Request $request, $format)
    {
        $gardenId = $this->gardenId($request);
        $rows = Attendance::with('kindergartener')->where('kindergarten_id', $gardenId)
            ->when($request->date_from, fn ($q) => $q->whereDate('attendance_date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('attendance_date', '<=', $request->date_to))
            ->orderBy('attendance_date')->get();
        if ($format === 'pdf') return view('attendance.report', compact('rows'));
        if ($format === 'xlsx') return Excel::download(new AttendanceExport($gardenId,$request->date_from,$request->date_to),'attendance.xlsx');
        abort_unless($format === 'csv', 404);
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['თარიღი','ბავშვი','პირადი ნომერი','სტატუსი']);
            foreach ($rows as $row) fputcsv($out, [$row->attendance_date->format('Y-m-d'), $row->kindergartener->kids_first_name.' '.$row->kindergartener->kids_last_name, $row->kindergartener->kids_personal_number, $row->status_label]);
            fclose($out);
        }, 'attendance.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function evaluate(Request $request)
    {
        $data = $request->validate(['kindergarten_id' => ['required', 'exists:kindergartens,id']]);
        $before = Kindergartener::where('kindergarten_id', $data['kindergarten_id'])->where('application_status', 'suspended')->count();
        Artisan::call('attendance:evaluate', ['--kindergarten' => $data['kindergarten_id']]);
        $after = Kindergartener::where('kindergarten_id', $data['kindergarten_id'])->where('application_status', 'suspended')->count();
        $suspended = max(0, $after - $before);

        $this->logAudit('attendance.evaluate', Attendance::class, null, 'Attendance absence rules evaluated manually', [
            'kindergarten_id' => (int) $data['kindergarten_id'],
            'suspended_count' => $suspended,
        ]);

        return back()->with([
            'flashType' => 'success',
            'flashMessage' => $suspended
                ? "შემოწმება დასრულდა: შეჩერდა {$suspended} რეგისტრაცია და SMS შეტყობინება გაგზავნის რიგში ჩადგა."
                : 'შემოწმება დასრულდა: შეჩერების პირობას არცერთი ახალი ბავშვი არ აკმაყოფილებს.',
        ]);
    }

    private function gardenId(Request $request, bool $required = true)
    {
        $user = $request->user();
        $id = $user->isUnionAdmin() ? ($request->input('kindergarten_id') ?: $user->kindergarten_id ?: Kindergarten::value('id')) : $user->kindergarten_id;
        if (!$id && $required) abort(422, 'ბაღი არ არის არჩეული.');
        return $id ? (int) $id : null;
    }

    private function attendanceRisks($children, int $gardenId, Carbon $date)
    {
        if ($children->isEmpty()) return collect();

        $calendar = app(WorkCalendarService::class);
        $anchor = $date->copy()->min(today());
        $from = $anchor->copy()->startOfMonth()->min($anchor->copy()->subDays(45))->toDateString();
        $records = Attendance::whereIn('kindergartener_id', $children->pluck('id'))
            ->whereDate('attendance_date', '>=', $from)->whereDate('attendance_date', '<=', $anchor)
            ->get()->groupBy('kindergartener_id');

        return $children->mapWithKeys(function ($child) use ($records, $calendar, $gardenId, $anchor) {
            $childRecords = $records->get($child->id, collect());
            $byDate = $childRecords->keyBy(fn ($attendance) => $attendance->attendance_date->toDateString());
            $monthly = $childRecords->filter(fn ($attendance) =>
                $attendance->status === Attendance::ABSENT
                && $attendance->attendance_date->isSameMonth($anchor)
                && $calendar->isWorkingDay($attendance->attendance_date, $gardenId)
            )->count();
            $cursor = $anchor->copy();
            $streak = 0;
            for ($days = 0; $days < 45; $days++, $cursor->subDay()) {
                if (!$calendar->isWorkingDay($cursor, $gardenId)) continue;
                $record = $byDate->get($cursor->toDateString());
                if (!$record && $cursor->isSameDay($anchor)) continue;
                if (!$record || $record->status !== Attendance::ABSENT) break;
                $streak++;
            }
            if ($streak < 7 && $monthly < 12) return [];
            $remaining = min(max(0, 10 - $streak), max(0, 15 - $monthly));
            return [$child->id => (object) ['streak' => $streak, 'monthly' => $monthly,
                'remaining' => $remaining, 'level' => $remaining <= 1 ? 'critical' : 'warning']];
        });
    }
}
