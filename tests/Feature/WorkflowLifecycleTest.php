<?php

namespace Tests\Feature;

use App\Exports\AttendanceExport;
use App\Exports\KindergartenerExport;
use App\Model\API\Kindergartener;
use App\Model\Attendance;
use App\Model\PlacementOffer;
use App\Model\ReinstatementRequest;
use App\Model\WorkCalendarDay;
use App\Services\ApplicationWorkflowService;
use App\Services\ParentNotificationService;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkflowLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiting_list_reserves_the_first_eligible_child(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);

        $enrolled = $this->createChild($workflow, $municipality, $garden, '01000000101');
        $waiting = $this->createChild($workflow, $municipality, $garden, '01000000102', ['priority_id' => 1, 'has_permission' => true]);

        $this->assertSame('enrolled', $enrolled->application_status);
        $this->assertSame('waiting', $waiting->application_status);

        $workflow->transition($enrolled, 'cancelled', 'Test vacancy');
        Artisan::call('waiting-list:process');

        $entry = $waiting->fresh()->waitingListEntry;
        $this->assertSame('offered', $entry->state);
        $this->assertDatabaseHas('placement_offers', ['waiting_list_entry_id' => $entry->id]);
        $this->assertDatabaseHas('kindergarten_group_age_range', [
            'kindergarten_id' => $garden,
            'group_age_range' => 1,
            'space_free' => 1,
            'space_reserved' => 1,
        ]);
    }

    public function test_accepting_a_placement_offer_enrolls_once_and_consumes_reservation(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $enrolled = $this->createChild($workflow, $municipality, $garden, '01000000201');
        $waiting = $this->createChild($workflow, $municipality, $garden, '01000000202');

        $workflow->transition($enrolled, 'cancelled', 'Test vacancy');
        Artisan::call('waiting-list:process');
        $entry = $waiting->fresh()->waitingListEntry;
        $offer = PlacementOffer::where('waiting_list_entry_id', $entry->id)->firstOrFail();

        $workflow->acceptReservedPlacement($waiting->fresh(), $entry);

        $this->assertSame('enrolled', $waiting->fresh()->application_status);
        $this->assertSame('completed', $entry->fresh()->state);
        $this->assertDatabaseHas('kindergarten_group_age_range', [
            'kindergarten_id' => $garden,
            'group_age_range' => 1,
            'space_filled' => 1,
            'space_free' => 0,
            'space_reserved' => 0,
        ]);
        $this->assertNotNull($offer->token_hash);
    }

    public function test_ten_consecutive_working_day_absences_suspend_registration_once(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000301');

        $date = today()->copy();
        $recorded = 0;
        while ($recorded < 10) {
            if (!$date->isWeekend()) {
                Attendance::create([
                    'kindergartener_id' => $child->id,
                    'kindergarten_id' => $garden,
                    'group_id' => 1,
                    'attendance_date' => $date->toDateString(),
                    'status' => 'absent',
                ]);
                $recorded++;
            }
            $date->subDay();
        }

        Artisan::call('attendance:evaluate');
        Artisan::call('attendance:evaluate');

        $this->assertSame('suspended', $child->fresh()->application_status);
        $this->assertSame(1, ReinstatementRequest::where('kindergartener_id', $child->id)->count());
    }

    public function test_union_admin_can_run_attendance_evaluation_before_schedule(): void
    {
        config(['services.mailtrap.api_token' => null]);
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000303');
        $admin = User::create(['name'=>'Admin','email'=>'attendance-admin@example.test','password'=>Hash::make('password'),'role'=>'union_admin']);
        DB::table('settings')->insert([
            ['slug'=>'basic','object'=>json_encode(['isRegistrationStart'=>true,'isLearningStart'=>true,'canPorting'=>false]),'created_at'=>now(),'updated_at'=>now()],
            ['slug'=>'date','object'=>json_encode(['start'=>'2026-09-01','end'=>'2027-06-15']),'created_at'=>now(),'updated_at'=>now()],
        ]);
        $date = today()->copy();
        $recorded = 0;

        while ($recorded < 10) {
            if (!$date->isWeekend()) {
                Attendance::create(['kindergartener_id'=>$child->id,'kindergarten_id'=>$garden,'group_id'=>1,'attendance_date'=>$date->toDateString(),'status'=>'absent']);
                $recorded++;
            }
            $date->subDay();
        }

        $this->actingAs($admin)->post(route('attendance.evaluate'), ['kindergarten_id'=>$garden])->assertRedirect();

        $this->assertSame('suspended', $child->fresh()->application_status);
        $this->assertDatabaseHas('audit_logs', ['action'=>'attendance.evaluate','user_id'=>$admin->id]);
    }

    public function test_monthly_absence_limit_ignores_non_working_calendar_days(): void
    {
        Carbon::setTestNow('2026-09-30 21:00:00');
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000302');
        $date = now()->startOfMonth();
        $recordedDates = [];

        while (count($recordedDates) < 15) {
            if (!$date->isWeekend()) {
                $recordedDates[] = $date->toDateString();
                Attendance::create([
                    'kindergartener_id' => $child->id,
                    'kindergarten_id' => $garden,
                    'group_id' => 1,
                    'attendance_date' => $date->toDateString(),
                    'status' => 'absent',
                ]);
            }
            $date->addDay();
        }

        WorkCalendarDay::create([
            'date' => $recordedDates[0],
            'kindergarten_id' => $garden,
            'is_working_day' => false,
            'label' => 'Test closure',
        ]);

        Artisan::call('attendance:evaluate');

        $this->assertSame('enrolled', $child->fresh()->application_status);
        $this->assertSame(0, ReinstatementRequest::where('kindergartener_id', $child->id)->count());
        Carbon::setTestNow();
    }

    public function test_approved_reinstatement_marks_absences_excused_and_restores_enrollment(): void
    {
        $notifications = \Mockery::mock(ParentNotificationService::class);
        $notifications->shouldReceive('send')->once()->withArgs(function ($sentChild, $event, $subject, $message) use (&$child) {
            return $sentChild->id === $child->id
                && $event === 'reinstatement_approved'
                && $subject === 'აღდგენის მოთხოვნის პასუხი'
                && str_contains($message, 'დოკუმენტი სრულად შეესაბამება მოთხოვნებს.');
        });
        $this->app->instance(ParentNotificationService::class, $notifications);
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $workflow = app(ApplicationWorkflowService::class);
        $child = $this->createChild($workflow, $municipality, $garden, '01000000401');
        $workflow->transition($child, 'suspended', 'Test suspension');

        $from = today()->subDays(2)->toDateString();
        Attendance::create(['kindergartener_id' => $child->id, 'kindergarten_id' => $garden, 'group_id' => 1, 'attendance_date' => $from, 'status' => 'absent']);
        Attendance::create(['kindergartener_id' => $child->id, 'kindergarten_id' => $garden, 'group_id' => 1, 'attendance_date' => today()->toDateString(), 'status' => 'absent']);
        $request = ReinstatementRequest::create([
            'kindergartener_id' => $child->id,
            'token_hash' => hash('sha256', Str::random(64)),
            'expires_at' => now()->addDays(5),
            'absence_from' => $from,
            'absence_to' => today()->toDateString(),
        ]);
        DB::table('settings')->insert([
            ['slug' => 'basic', 'object' => json_encode(['canPorting' => false, 'isLearningStart' => true]), 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'date', 'object' => json_encode(['start' => '2026-09-01', 'end' => '2027-06-15']), 'created_at' => now(), 'updated_at' => now()],
        ]);
        $admin = User::create([
            'name' => 'Test administrator',
            'email' => 'admin@example.test',
            'password' => Hash::make('testing-password'),
            'role' => 'union_admin',
        ]);

        $this->actingAs($admin)
            ->post("/reinstatement-requests/{$request->id}/review", ['decision' => 'approved', 'review_note' => 'დოკუმენტი სრულად შეესაბამება მოთხოვნებს.'])
            ->assertRedirect();

        $this->assertSame('enrolled', $child->fresh()->application_status);
        $this->assertSame('approved', $request->fresh()->status);
        $this->assertSame(0, Attendance::where('kindergartener_id', $child->id)->where('status', 'absent')->count());
        $this->assertSame(2, Attendance::where('kindergartener_id', $child->id)->where('status', 'excused')->count());
    }

    public function test_application_and_attendance_exports_use_georgian_status_labels(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000402');
        $attendance = Attendance::create([
            'kindergartener_id' => $child->id,
            'kindergarten_id' => $garden,
            'group_id' => 1,
            'attendance_date' => today()->toDateString(),
            'status' => 'absent',
        ]);

        $this->assertSame('ჩარიცხული', $child->application_status_label);
        $this->assertSame('გაცდენილი', $attendance->status_label);
        $this->assertSame('ჩარიცხული', (new KindergartenerExport($garden))->map($child)[3]);
        $this->assertSame('გაცდენილი', (new AttendanceExport($garden))->map($attendance)[3]);
        $this->assertStringContainsString('გაცდენილი', view('attendance.report', ['rows' => collect([$attendance])])->render());
    }

    private function gardenWithCapacity(int $capacity): array
    {
        $region = DB::table('regions')->insertGetId(['name' => 'Test region', 'created_at' => now(), 'updated_at' => now()]);
        $municipality = DB::table('municipalities')->insertGetId(['region_id' => $region, 'name' => 'Test municipality', 'created_at' => now(), 'updated_at' => now()]);
        $garden = DB::table('kindergartens')->insertGetId(['municipality_id' => $municipality, 'name' => 'Test garden', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('kindergarten_group_age_range')->insert(['kindergarten_id' => $garden, 'group_age_range' => 1, 'space_length' => $capacity, 'space_filled' => 0, 'space_free' => $capacity, 'space_reserved' => 0]);
        return [$municipality, $garden];
    }

    private function createChild(ApplicationWorkflowService $workflow, int $municipality, int $garden, string $personalNumber, array $extra = []): Kindergartener
    {
        return $workflow->save(array_merge([
            'kids_personal_number' => $personalNumber,
            'kids_first_name' => 'ანა',
            'kids_last_name' => 'ტესტი',
            'birth_date' => '2022-01-01',
            'mobile_number' => '555000000',
            'municipality_id' => $municipality,
            'kindergarten_id' => $garden,
            'group_id' => 1,
        ], $extra));
    }
}
