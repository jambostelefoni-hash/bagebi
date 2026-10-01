<?php

namespace App\Services;

use App\Model\DataQualityIssue;
use App\Model\DataQualityScan;
use App\Model\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DataQualityScanner
{
    private array $issues = [];

    public function run(string $source = 'scheduled', ?int $userId = null): DataQualityScan
    {
        $scan = DataQualityScan::create([
            'triggered_by' => $userId, 'source' => $source, 'status' => 'running', 'started_at' => now(),
        ]);

        try {
            $this->issues = [];
            $this->scanChildren();
            $this->scanQueuesAndOffers();
            $this->scanCapacities();
            $counts = collect($this->issues)->countBy('severity');

            DB::transaction(function () {
                DataQualityIssue::where('status', 'open')->update(['status' => 'resolved', 'resolved_at' => now()]);
                foreach ($this->issues as $issue) {
                    $existing = DataQualityIssue::where('fingerprint', $issue['fingerprint'])->first();
                    if ($existing) {
                        $existing->fill($issue + ['status' => 'open', 'last_detected_at' => now(), 'resolved_at' => null])->save();
                    } else {
                        DataQualityIssue::create($issue + ['status' => 'open', 'first_detected_at' => now(), 'last_detected_at' => now()]);
                    }
                }
            });

            $scan->update([
                'status' => 'completed', 'issues_found' => count($this->issues),
                'critical_count' => $counts->get('critical', 0), 'warning_count' => $counts->get('warning', 0),
                'info_count' => $counts->get('info', 0), 'finished_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $scan->update(['status' => 'failed', 'finished_at' => now(), 'error' => mb_substr($exception->getMessage(), 0, 2000)]);
            throw $exception;
        }

        return $scan->fresh();
    }

    private function scanChildren(): void
    {
        $gardenIds = DB::table('kindergartens')->pluck('id')->map(fn ($id) => (int) $id)->flip();
        $groups = DB::table('group_age_ranges')->pluck('range', 'id');
        $start = $this->schoolStart();
        $duplicates = DB::table('kindergarteners')->select('kids_personal_number')
            ->whereNotNull('kids_personal_number')->groupBy('kids_personal_number')->havingRaw('COUNT(*) > 1')
            ->pluck('kids_personal_number')->flip();

        DB::table('kindergarteners')->orderBy('id')->chunkById(200, function ($children) use ($gardenIds, $groups, $start, $duplicates) {
            foreach ($children as $child) {
                $name = trim($child->kids_first_name.' '.$child->kids_last_name) ?: 'ჩანაწერი #'.$child->id;
                $base = ['entity_type' => 'kindergartener', 'entity_id' => $child->id,
                    'kindergarten_id' => $child->kindergarten_id, 'details' => ['name' => $name]];
                if (!preg_match('/^\d{11}$/', (string) $child->kids_personal_number)) {
                    $this->add('invalid_personal_number', 'critical', $base, $name.' — ბავშვის პირადი ნომერი არასწორ ფორმატშია.');
                } elseif ($duplicates->has($child->kids_personal_number)) {
                    $this->add('duplicate_personal_number', 'critical', $base, $name.' — ბავშვის პირადი ნომერი დუბლირებულია.');
                }
                if (!$this->validMobile((string) $child->mobile_number)) {
                    $this->add('invalid_mobile', 'critical', $base, $name.' — მობილურის ნომერი SMS-ისთვის არასწორია.');
                }
                if (!trim((string) $child->kids_first_name) || !trim((string) $child->kids_last_name) || !$child->birth_date) {
                    $this->add('missing_required_data', 'critical', $base, $name.' — აუცილებელი მონაცემები სრულად არ არის შევსებული.');
                }
                if (!$child->kindergarten_id || !$gardenIds->has((int) $child->kindergarten_id)) {
                    $this->add('invalid_kindergarten', 'critical', $base, $name.' — მითითებული ბაღი აღარ არსებობს.');
                }
                if ($child->application_status !== 'graduated' && (!$child->group_id || !$groups->has($child->group_id))) {
                    $this->add('invalid_group', 'critical', $base, $name.' — ასაკობრივი ჯგუფი არასწორია ან აღარ არსებობს.');
                } elseif ($child->application_status !== 'graduated' && $start && $child->birth_date && preg_match('/^\s*(\d+)\s*[-–—]\s*(\d+)\s*$/u', $groups[$child->group_id], $match)) {
                    try {
                        $age = Carbon::parse($child->birth_date)->diffInYears($start, false);
                        if ($age < (int) $match[1] || $age >= (int) $match[2]) {
                            $this->add('age_group_mismatch', 'warning', $base, $name.' — ასაკი არ შეესაბამება ჯგუფს „'.$groups[$child->group_id].'“ .');
                        }
                    } catch (\Throwable $exception) {
                        $this->add('invalid_birth_date', 'critical', $base, $name.' — დაბადების თარიღი არასწორია.');
                    }
                }
                if ($child->application_status === 'graduated' && (!$child->graduate || $child->group_id)) {
                    $this->add('graduated_state_mismatch', 'warning', $base, $name.' — დასრულების სტატუსი და აქტიური ჯგუფი ერთმანეთს არ შეესაბამება.');
                }
            }
        });
    }

    private function scanQueuesAndOffers(): void
    {
        $children = DB::table('kindergarteners')->pluck('application_status', 'id');
        DB::table('waiting_list_entries')->orderBy('id')->chunkById(200, function ($entries) use ($children) {
            foreach ($entries as $entry) {
                $base = ['entity_type' => 'kindergartener', 'entity_id' => $entry->kindergartener_id,
                    'kindergarten_id' => $entry->kindergarten_id, 'details' => ['waiting_list_entry_id' => $entry->id]];
                $status = $children[$entry->kindergartener_id] ?? null;
                if (!$status || (in_array($entry->state, ['waiting', 'offered'], true) && $status !== 'waiting')) {
                    $this->add('queue_status_mismatch', 'critical', $base, 'რიგის ჩანაწერი და ბავშვის მიმდინარე სტატუსი ერთმანეთს არ შეესაბამება.');
                }
                if ($status === 'graduated' && in_array($entry->state, ['waiting', 'offered'], true)) {
                    $this->add('graduated_in_queue', 'critical', $base, 'ბაღდამთავრებული ბავშვი აქტიურ რიგშია დარჩენილი.');
                }
            }
        });

        DB::table('placement_offers')->join('waiting_list_entries', 'waiting_list_entries.id', '=', 'placement_offers.waiting_list_entry_id')
            ->whereNull('placement_offers.responded_at')->orderBy('placement_offers.id')
            ->select('placement_offers.*', 'waiting_list_entries.kindergartener_id', 'waiting_list_entries.kindergarten_id', 'waiting_list_entries.state')
            ->chunkById(200, function ($offers) {
                foreach ($offers as $offer) {
                    if ($offer->state !== 'offered') {
                        $this->add('offer_state_mismatch', 'critical', [
                            'entity_type' => 'kindergartener', 'entity_id' => $offer->kindergartener_id,
                            'kindergarten_id' => $offer->kindergarten_id, 'details' => ['offer_id' => $offer->id],
                        ], 'მოქმედ შეთავაზებას რიგში „შეთავაზებულია“ მდგომარეობა არ აქვს.');
                    }
                }
            }, 'placement_offers.id', 'id');
    }

    private function scanCapacities(): void
    {
        $occupied = DB::table('kindergarteners')->whereIn('application_status', ['enrolled', 'suspended'])
            ->select('kindergarten_id', 'group_id', DB::raw('COUNT(*) as total'))
            ->groupBy('kindergarten_id', 'group_id')->get()->keyBy(fn ($row) => $row->kindergarten_id.':'.$row->group_id);
        $activeOffers = DB::table('placement_offers')->join('waiting_list_entries', 'waiting_list_entries.id', '=', 'placement_offers.waiting_list_entry_id')
            ->whereNull('placement_offers.responded_at')->where('placement_offers.expires_at', '>', now())
            ->select('waiting_list_entries.kindergarten_id', 'waiting_list_entries.group_id', DB::raw('COUNT(*) as total'))
            ->groupBy('waiting_list_entries.kindergarten_id', 'waiting_list_entries.group_id')->get()
            ->keyBy(fn ($row) => $row->kindergarten_id.':'.$row->group_id);
        $seen = [];
        DB::table('kindergarten_group_age_range')->orderBy('kindergarten_id')->orderBy('group_age_range')->get()->each(function ($slot) use ($occupied, $activeOffers, &$seen) {
            $key = $slot->kindergarten_id.':'.$slot->group_age_range;
            $seen[$key] = true;
            $actual = (int) optional($occupied->get($key))->total;
            $reserved = (int) optional($activeOffers->get($key))->total;
            $base = ['entity_type' => 'capacity', 'entity_id' => null, 'kindergarten_id' => $slot->kindergarten_id,
                'details' => ['group_id' => $slot->group_age_range]];
            if ((int) $slot->space_length < $actual + $reserved) {
                $this->add('capacity_exceeded', 'critical', $base, 'ჯგუფში ჩარიცხული და დაჯავშნილი ადგილები მითითებულ ზღვარს აჭარბებს.');
            }
            if ((int) $slot->space_filled !== $actual || (int) $slot->space_reserved !== $reserved ||
                (int) $slot->space_free !== max(0, (int) $slot->space_length - $actual)) {
                $this->add('capacity_count_mismatch', 'warning', $base, 'ტევადობის შენახული მაჩვენებლები რეალურ ჩანაწერებს არ ემთხვევა.');
            }
        });
        foreach ($occupied as $key => $row) {
            if (!isset($seen[$key])) {
                $this->add('missing_capacity', 'critical', ['entity_type' => 'capacity', 'entity_id' => null,
                    'kindergarten_id' => $row->kindergarten_id, 'details' => ['group_id' => $row->group_id]],
                    'აქტიურ ჯგუფს ზღვრული რაოდენობა არ აქვს მითითებული.');
            }
        }
    }

    private function add(string $type, string $severity, array $base, string $message): void
    {
        $identity = $type.'|'.($base['entity_type'] ?? '').'|'.($base['entity_id'] ?? '').'|'.($base['kindergarten_id'] ?? '').'|'.json_encode($base['details'] ?? []);
        $this->issues[] = $base + ['fingerprint' => hash('sha256', $identity), 'type' => $type,
            'severity' => $severity, 'message' => $message];
    }

    private function validMobile(string $mobile): bool
    {
        $digits = preg_replace('/\D+/', '', $mobile);
        if (str_starts_with($digits, '00')) $digits = substr($digits, 2);
        if (str_starts_with($digits, '0')) $digits = substr($digits, 1);
        return (strlen($digits) === 9 && str_starts_with($digits, '5')) ||
            (strlen($digits) === 12 && str_starts_with($digits, '9955'));
    }

    private function schoolStart(): ?Carbon
    {
        try {
            $value = data_get(Setting::where('slug', 'date')->first(), 'object.start');
            return $value ? Carbon::parse($value)->startOfDay() : null;
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
