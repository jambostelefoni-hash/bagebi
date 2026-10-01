<?php

namespace App\Http\Controllers;

use App\Model\DataQualityIssue;
use App\Model\DataQualityScan;
use App\Model\Kindergarten;
use App\Services\DataQualityScanner;
use Illuminate\Http\Request;

class DataQualityController extends Controller
{
    public function index(Request $request)
    {
        $query = DataQualityIssue::with('kindergarten')->latest('last_detected_at');
        foreach (['type', 'severity', 'status'] as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }
        if ($request->filled('kindergarten_id')) $query->where('kindergarten_id', $request->kindergarten_id);

        return view('data-quality.index', [
            'issues' => $query->paginate(25)->appends($request->query()),
            'summary' => [
                'open' => DataQualityIssue::where('status', 'open')->count(),
                'critical' => DataQualityIssue::where('status', 'open')->where('severity', 'critical')->count(),
                'warning' => DataQualityIssue::where('status', 'open')->where('severity', 'warning')->count(),
                'resolved' => DataQualityIssue::where('status', 'resolved')->count(),
            ],
            'lastScan' => DataQualityScan::latest('started_at')->first(),
            'gardens' => Kindergarten::orderBy('name')->pluck('name', 'id'),
            'types' => DataQualityIssue::query()->distinct()->orderBy('type')->pluck('type'),
            'typeLabels' => $this->typeLabels(),
        ]);
    }

    public function scan(DataQualityScanner $scanner)
    {
        $scan = $scanner->run('manual', auth()->id());
        $this->logAudit('data_quality.scan', DataQualityScan::class, $scan->id,
            'მონაცემების ხარისხის ხელით შემოწმება', [
                'issues_found' => $scan->issues_found, 'critical' => $scan->critical_count,
                'warning' => $scan->warning_count, 'info' => $scan->info_count,
            ], true);

        return back()->with(['flashType' => 'success',
            'flashMessage' => 'შემოწმება დასრულდა: აღმოჩენილია '.$scan->issues_found.' საკითხი.']);
    }

    public function edit(DataQualityIssue $issue)
    {
        if ($issue->entity_type === 'kindergartener' && $issue->entity_id) {
            return redirect()->route('kindergarteners.show', $issue->entity_id);
        }
        if ($issue->entity_type === 'capacity' && $issue->kindergarten_id) {
            return redirect()->route('kindergartens.show', $issue->kindergarten_id);
        }
        return redirect()->route('data-quality.index')->withErrors(['issue' => 'ამ საკითხისთვის რედაქტირების გვერდი ვერ მოიძებნა.']);
    }

    private function typeLabels(): array
    {
        return [
            'invalid_personal_number' => 'არასწორი პირადი ნომერი', 'duplicate_personal_number' => 'დუბლირებული პირადი ნომერი',
            'invalid_mobile' => 'არასწორი მობილური', 'missing_required_data' => 'არასრული მონაცემები',
            'invalid_kindergarten' => 'არასწორი ბაღი', 'invalid_group' => 'არასწორი ჯგუფი',
            'age_group_mismatch' => 'ასაკი და ჯგუფი', 'invalid_birth_date' => 'არასწორი დაბადების თარიღი',
            'graduated_state_mismatch' => 'დასრულების მდგომარეობა', 'queue_status_mismatch' => 'რიგი და სტატუსი',
            'graduated_in_queue' => 'დამთავრებული აქტიურ რიგში', 'offer_state_mismatch' => 'შეთავაზების მდგომარეობა',
            'capacity_exceeded' => 'ზღვარი გადაჭარბებულია', 'capacity_count_mismatch' => 'ტევადობის დათვლა',
            'missing_capacity' => 'ზღვარი არ არის მითითებული',
        ];
    }
}
