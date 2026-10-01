<?php

namespace App\Http\Controllers;

use App\Model\GroupAgeRange;
use App\Model\Kindergarten;
use App\Model\WaitingListEntry;
use App\Model\API\Kindergartener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'kindergarten_id' => ['nullable', 'integer', 'exists:kindergartens,id'],
            'group_id' => ['nullable', 'integer', 'exists:group_age_ranges,id'],
        ]);

        $active = DB::table('kindergarteners')
            ->select('kindergarten_id', 'group_id',
                DB::raw("SUM(application_status = 'enrolled') as enrolled_count"),
                DB::raw("SUM(application_status = 'suspended') as suspended_count"))
            ->whereIn('application_status', ['enrolled', 'suspended'])
            ->groupBy('kindergarten_id', 'group_id');

        $waiting = WaitingListEntry::query()
            ->select('kindergarten_id', 'group_id', DB::raw('COUNT(*) as waiting_count'))
            ->where('state', 'waiting')
            ->groupBy('kindergarten_id', 'group_id');

        $rows = DB::table('kindergarten_group_age_range as capacity')
            ->join('kindergartens as gardens', 'gardens.id', '=', 'capacity.kindergarten_id')
            ->join('group_age_ranges as groups', 'groups.id', '=', 'capacity.group_age_range')
            ->leftJoinSub($active, 'active', function ($join) {
                $join->on('active.kindergarten_id', '=', 'capacity.kindergarten_id')
                    ->on('active.group_id', '=', 'capacity.group_age_range');
            })
            ->leftJoinSub($waiting, 'waiting', function ($join) {
                $join->on('waiting.kindergarten_id', '=', 'capacity.kindergarten_id')
                    ->on('waiting.group_id', '=', 'capacity.group_age_range');
            })
            ->when($request->kindergarten_id, fn ($query, $id) => $query->where('capacity.kindergarten_id', $id))
            ->when($request->group_id, fn ($query, $id) => $query->where('capacity.group_age_range', $id))
            ->orderBy('gardens.name')->orderBy('groups.range')
            ->get([
                'capacity.kindergarten_id', 'capacity.group_age_range as group_id', 'capacity.space_length', 'capacity.space_reserved',
                'gardens.name as kindergarten_name', 'groups.range as group_range',
                DB::raw('COALESCE(active.enrolled_count, 0) as enrolled_count'),
                DB::raw('COALESCE(active.suspended_count, 0) as suspended_count'),
                DB::raw('COALESCE(waiting.waiting_count, 0) as waiting_count'),
            ])->map(function ($row) {
                $row->active_count = (int) $row->enrolled_count + (int) $row->suspended_count;
                $row->available_count = max(0, (int) $row->space_length - $row->active_count - (int) $row->space_reserved);
                $row->occupancy = (int) $row->space_length > 0 ? round(($row->active_count / $row->space_length) * 100) : 0;
                $row->state = $row->occupancy >= 100 ? 'full' : ($row->occupancy >= 85 ? 'warning' : 'normal');
                return $row;
            });

        return view('analytics.registration', [
            'rows' => $rows,
            'gardens' => Kindergarten::orderBy('name')->pluck('name', 'id'),
            'groups' => GroupAgeRange::orderBy('range')->pluck('range', 'id'),
            'summary' => [
                'full' => $rows->where('state', 'full')->count(),
                'warning' => $rows->where('state', 'warning')->count(),
                'available' => $rows->sum('available_count'),
                'waiting' => $rows->sum('waiting_count'),
            ],
            'forecast' => $this->forecast(),
        ]);
    }

    private function forecast()
    {
        $groups = GroupAgeRange::orderBy('id')->get()->keyBy('id');
        $byRange = [];
        foreach ($groups as $group) {
            if ($range = $this->range($group->range)) $byRange[$range[0].'-'.$range[1]][] = $group->id;
        }

        $projected = [];
        $unmapped = [];
        Kindergartener::whereIn('application_status', ['enrolled', 'suspended'])
            ->select('kindergarten_id', 'group_id', DB::raw('COUNT(*) as total'))
            ->groupBy('kindergarten_id', 'group_id')->get()
            ->each(function ($source) use (&$projected, &$unmapped, $groups, $byRange) {
                $group = $groups->get($source->group_id);
                $range = $group ? $this->range($group->range) : null;
                if (!$range || $range[0] >= 5) return;
                $targets = $byRange[($range[0] + 1).'-'.($range[1] + 1)] ?? [];
                if (count($targets) !== 1) {
                    $unmapped[] = $group->range;
                    return;
                }
                $key = $source->kindergarten_id.':'.$targets[0];
                $projected[$key] = ($projected[$key] ?? 0) + (int) $source->total;
            });

        $capacities = DB::table('kindergarten_group_age_range as capacity')
            ->join('kindergartens as gardens', 'gardens.id', '=', 'capacity.kindergarten_id')
            ->join('group_age_ranges as groups', 'groups.id', '=', 'capacity.group_age_range')
            ->orderBy('gardens.name')->orderBy('groups.range')
            ->get(['capacity.kindergarten_id', 'capacity.group_age_range as group_id', 'capacity.space_length', 'gardens.name as kindergarten_name', 'groups.range as group_range']);

        return [
            'rows' => $capacities->map(function ($row) use ($projected) {
                $row->projected_count = $projected[$row->kindergarten_id.':'.$row->group_id] ?? 0;
                $row->forecast_available = (int) $row->space_length - $row->projected_count;
                return $row;
            })->filter(fn ($row) => $row->projected_count > 0)->values(),
            'unmapped' => array_values(array_unique($unmapped)),
        ];
    }

    private function range(string $label): ?array
    {
        if (!preg_match('/^\s*([2-5])\s*[-–—]\s*([3-6])\s*$/u', $label, $match)) return null;
        return (int) $match[2] > (int) $match[1] ? [(int) $match[1], (int) $match[2]] : null;
    }
}
