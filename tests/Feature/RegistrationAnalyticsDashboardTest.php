<?php

namespace Tests\Feature;

use App\Model\API\Kindergartener;
use App\Model\Attendance;
use App\Model\Setting;
use App\Model\WaitingListEntry;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Tests\TestCase;

class RegistrationAnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::create(['slug' => 'basic', 'object' => ['isRegistrationStart' => true, 'isLearningStart' => true]]);
    }

    public function test_union_admin_sees_capacity_waiting_and_forecast_data(): void
    {
        $garden = $this->garden('ანალიტიკის ბაღი', 10);
        $this->child($garden, '01000000001', 'enrolled');
        $this->child($garden, '01000000002', 'suspended');
        $waitingChild = $this->child($garden, '01000000003', 'waiting');
        WaitingListEntry::create(['kindergartener_id'=>$waitingChild->id, 'kindergarten_id'=>$garden, 'group_id'=>1, 'priority_rank'=>999, 'queued_at'=>now(), 'state'=>'waiting']);
        $admin = $this->user('union_admin');

        $this->actingAs($admin)->get(route('analytics.registration'))
            ->assertOk()
            ->assertSee('ანალიტიკის ბაღი')
            ->assertSee('მომდევნო სასწავლო წლის პროგნოზი', false);
    }

    public function test_director_dashboard_only_contains_own_garden_tasks(): void
    {
        Carbon::setTestNow('2026-09-18 10:00:00');
        $ownGarden = $this->garden('ჩემი ბაღი', 5);
        $otherGarden = $this->garden('სხვა ბაღი', 5);
        $ownChild = $this->child($ownGarden, '01000000011', 'enrolled', 'თამარი');
        $otherChild = $this->child($otherGarden, '01000000012', 'enrolled', 'სხვა');
        Attendance::create(['kindergartener_id'=>$ownChild->id, 'kindergarten_id'=>$ownGarden, 'group_id'=>1, 'attendance_date'=>today(), 'status'=>'absent']);
        $cursor = today()->subDay();
        for ($recorded = 1; $recorded < 7; $cursor->subDay()) {
            if (!app(\App\Services\WorkCalendarService::class)->isWorkingDay($cursor, $ownGarden)) continue;
            Attendance::create(['kindergartener_id'=>$ownChild->id, 'kindergarten_id'=>$ownGarden, 'group_id'=>1, 'attendance_date'=>$cursor->copy(), 'status'=>'absent']);
            $recorded++;
        }
        for ($number = 13; $number <= 20; $number++) {
            $child = $this->child($ownGarden, '010000000'.$number, 'enrolled', 'ტესტი'.$number);
            Attendance::create(['kindergartener_id'=>$child->id, 'kindergarten_id'=>$ownGarden, 'group_id'=>1, 'attendance_date'=>today(), 'status'=>'absent']);
        }
        Attendance::create(['kindergartener_id'=>$otherChild->id, 'kindergarten_id'=>$otherGarden, 'group_id'=>1, 'attendance_date'=>today(), 'status'=>'absent']);
        $director = $this->user('director', $ownGarden);

        $this->actingAs($director)->get(route('home'))
            ->assertOk()
            ->assertSee('თამარი')
            ->assertSee('9 ჩანაწერი')
            ->assertSee('ყველა გაცდენილის ნახვა')
            ->assertSee('შეჩერების რისკის ქვეშ')
            ->assertSee('შეჩერებამდე დარჩა 3 დღე')
            ->assertDontSee('სხვა');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_director_cannot_open_union_analytics(): void
    {
        $director = $this->user('director', $this->garden('ჩემი ბაღი', 5));
        $this->actingAs($director)->get(route('analytics.registration'))->assertForbidden();
    }

    public function test_porting_preview_lists_moves_without_changing_child_data(): void
    {
        $garden = $this->garden('პორტირების ბაღი', 5);
        $child = $this->child($garden, '01000000031', 'enrolled', 'პორტირება');
        Setting::where('slug', 'basic')->update(['object' => ['isLearningStart' => false, 'canPorting' => true]]);
        Setting::create(['slug' => 'date', 'object' => ['start' => '2025-09-01', 'end' => '2026-06-15']]);
        $admin = $this->user('union_admin');

        $this->actingAs($admin)->get(route('settings.porting-preview'))
            ->assertOk()
            ->assertSee('პორტირება ტესტი')
            ->assertSee('3-4 წ.');

        $this->assertSame(1, $child->fresh()->group_id);
    }

    private function garden(string $name, int $capacity): int
    {
        $region = DB::table('regions')->insertGetId(['name'=>Str::random(8), 'created_at'=>now(), 'updated_at'=>now()]);
        $municipality = DB::table('municipalities')->insertGetId(['region_id'=>$region, 'name'=>Str::random(8), 'created_at'=>now(), 'updated_at'=>now()]);
        $garden = DB::table('kindergartens')->insertGetId(['municipality_id'=>$municipality, 'name'=>$name, 'created_at'=>now(), 'updated_at'=>now()]);
        DB::table('kindergarten_group_age_range')->insert(['kindergarten_id'=>$garden, 'group_age_range'=>1, 'space_length'=>$capacity, 'space_filled'=>0, 'space_free'=>$capacity, 'space_reserved'=>0]);
        DB::table('kindergarten_group_age_range')->insert(['kindergarten_id'=>$garden, 'group_age_range'=>2, 'space_length'=>$capacity, 'space_filled'=>0, 'space_free'=>$capacity, 'space_reserved'=>0]);
        return $garden;
    }

    private function child(int $garden, string $personalNumber, string $status, string $name = 'ანა'): Kindergartener
    {
        return Kindergartener::create(['kids_personal_number'=>$personalNumber, 'kids_first_name'=>$name, 'kids_last_name'=>'ტესტი', 'mobile_number'=>'555000000', 'kindergarten_id'=>$garden, 'group_id'=>1, 'application_status'=>$status]);
    }

    private function user(string $role, ?int $garden = null): User
    {
        return User::create(['name'=>'ტესტი', 'email'=>Str::random(8).'@example.test', 'password'=>Hash::make('password'), 'role'=>$role, 'kindergarten_id'=>$garden]);
    }
}
