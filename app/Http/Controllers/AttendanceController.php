<?php

namespace App\Http\Controllers;

use App\Model\API\Kindergartener;
use App\Model\Attendance;
use App\Model\GroupAgeRange;
use App\Model\Kindergarten;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $gardenId = $this->gardenId($request);
        $date = $request->input('date', now()->toDateString());
        $groupId = $request->input('group_id');
        $children = Kindergartener::where('kindergarten_id', $gardenId)
            ->where('application_status', 'enrolled')
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->with(['attendances' => fn ($q) => $q->whereDate('attendance_date', $date)])
            ->orderBy('kids_last_name')->get();

        return view('attendance.index', [
            'children' => $children, 'date' => $date, 'groupId' => $groupId,
            'groups' => GroupAgeRange::pluck('range', 'id'),
            'gardens' => auth()->user()->isUnionAdmin() ? Kindergarten::pluck('name', 'id') : collect(),
            'gardenId' => $gardenId,
        ]);
    }

    public function store(Request $request)
    {
        $gardenId = $this->gardenId($request);
        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'records' => ['required', 'array'],
            'records.*' => ['required', Rule::in(['present','absent','excused','non_working'])],
        ]);

        DB::transaction(function () use ($data, $gardenId) {
            $children = Kindergartener::where('kindergarten_id', $gardenId)->whereIn('id', array_keys($data['records']))->get()->keyBy('id');
            abort_unless($children->count() === count($data['records']), 403);
            foreach ($data['records'] as $id => $status) {
                $child = $children->get($id);
                Attendance::updateOrCreate(
                    ['kindergartener_id' => $child->id, 'attendance_date' => $data['date']],
                    ['kindergarten_id' => $gardenId, 'group_id' => $child->group_id, 'status' => $status, 'recorded_by' => auth()->id()]
                );
            }
        });
        $this->logAudit('attendance.store', Attendance::class, null, 'Attendance recorded', ['date' => $data['date'], 'kindergarten_id' => $gardenId, 'count' => count($data['records'])]);
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
                ? "შემოწმება დასრულდა: შეჩერდა {$suspended} რეგისტრაცია და ელფოსტის შეტყობინებები რიგში ჩადგა."
                : 'შემოწმება დასრულდა: შეჩერების პირობას არცერთი ახალი ბავშვი არ აკმაყოფილებს.',
        ]);
    }

    private function gardenId(Request $request)
    {
        $user = $request->user();
        $id = $user->isUnionAdmin() ? ($request->input('kindergarten_id') ?: $user->kindergarten_id ?: Kindergarten::value('id')) : $user->kindergarten_id;
        abort_unless($id, 422, 'ბაღი არ არის არჩეული.');
        return (int) $id;
    }
}
