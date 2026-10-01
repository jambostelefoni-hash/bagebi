<?php

namespace Tests\Feature;

use App\Model\DataQualityIssue;
use App\Model\Setting;
use App\Services\DataQualityScanner;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DataQualityControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::create(['slug' => 'basic', 'object' => ['isRegistrationStart' => false]]);
    }

    protected function beforeRefreshingDatabase()
    {
        if (config('database.connections.'.config('database.default').'.database') !== 'bagebi_testing') {
            throw new \RuntimeException('This test requires the isolated bagebi_testing database.');
        }
    }

    public function test_only_union_admin_can_open_and_run_quality_control(): void
    {
        $director = User::create(['name' => 'Director', 'email' => 'director-quality@example.test',
            'password' => bcrypt('password'), 'role' => 'director']);
        $this->actingAs($director)->get(route('data-quality.index'))->assertForbidden();
        $this->actingAs($director)->post(route('data-quality.scan'))->assertForbidden();

        $admin = User::create(['name' => 'Admin', 'email' => 'admin-quality@example.test',
            'password' => bcrypt('password'), 'role' => 'union_admin']);
        $this->actingAs($admin)->get(route('data-quality.index'))->assertOk();
    }

    public function test_scan_detects_does_not_mutate_and_resolves_fixed_problem_without_duplicates(): void
    {
        [$garden, $group] = $this->structure();
        $child = DB::table('kindergarteners')->insertGetId([
            'municipality_id' => DB::table('kindergartens')->where('id', $garden)->value('municipality_id'),
            'kindergarten_id' => $garden, 'group_id' => $group, 'active_status_id' => 2,
            'application_status' => 'enrolled', 'graduate' => false, 'birth_date' => '2023-01-10',
            'kids_personal_number' => '01001001001', 'kids_first_name' => 'ტესტი', 'kids_last_name' => 'ბავშვი',
            'mobile_number' => '123', 'created_at' => now(), 'updated_at' => now(),
        ]);

        app(DataQualityScanner::class)->run('scheduled');
        $this->assertDatabaseHas('data_quality_issues', ['type' => 'invalid_mobile', 'entity_id' => $child, 'status' => 'open']);
        $this->assertSame('123', DB::table('kindergarteners')->where('id', $child)->value('mobile_number'));
        $count = DataQualityIssue::count();

        app(DataQualityScanner::class)->run('scheduled');
        $this->assertSame($count, DataQualityIssue::count());

        DB::table('kindergarteners')->where('id', $child)->update(['mobile_number' => '551120240']);
        app(DataQualityScanner::class)->run('scheduled');
        $this->assertDatabaseHas('data_quality_issues', ['type' => 'invalid_mobile', 'entity_id' => $child, 'status' => 'resolved']);
    }

    public function test_manual_scan_is_audited(): void
    {
        $this->structure();
        $admin = User::create(['name' => 'Admin', 'email' => 'quality-audit@example.test',
            'password' => bcrypt('password'), 'role' => 'union_admin']);
        $this->actingAs($admin)->post(route('data-quality.scan'))->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'data_quality.scan']);
    }

    public function test_consistent_capacity_with_reservation_is_not_reported_as_a_mismatch(): void
    {
        [$garden, $group] = $this->structure();
        $municipality = DB::table('kindergartens')->where('id',$garden)->value('municipality_id');
        foreach (['01001002001','01001002002'] as $number) {
            DB::table('kindergarteners')->insert([
                'municipality_id'=>$municipality,'kindergarten_id'=>$garden,'group_id'=>$group,
                'active_status_id'=>2,'application_status'=>'enrolled','graduate'=>false,
                'birth_date'=>'2023-01-10','kids_personal_number'=>$number,'kids_first_name'=>'ტესტი',
                'kids_last_name'=>'ბავშვი','mobile_number'=>'551120240','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        $waitingId = DB::table('kindergarteners')->insertGetId([
            'municipality_id'=>$municipality,'kindergarten_id'=>$garden,'group_id'=>$group,
            'active_status_id'=>1,'application_status'=>'waiting','graduate'=>false,
            'birth_date'=>'2023-01-10','kids_personal_number'=>'01001002003','kids_first_name'=>'რიგის',
            'kids_last_name'=>'ბავშვი','mobile_number'=>'551120240','created_at'=>now(),'updated_at'=>now(),
        ]);
        $entryId = DB::table('waiting_list_entries')->insertGetId([
            'kindergartener_id'=>$waitingId,'kindergarten_id'=>$garden,'group_id'=>$group,
            'priority_rank'=>999,'queued_at'=>now(),'state'=>'offered','created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('placement_offers')->insert([
            'waiting_list_entry_id'=>$entryId,'token_hash'=>hash('sha256','quality-offer'),
            'expires_at'=>now()->addDay(),'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('kindergarten_group_age_range')->where('kindergarten_id',$garden)->where('group_age_range',$group)
            ->update(['space_length'=>10,'space_filled'=>2,'space_free'=>8,'space_reserved'=>1]);

        app(DataQualityScanner::class)->run('scheduled');

        $this->assertDatabaseMissing('data_quality_issues', ['type'=>'capacity_count_mismatch','kindergarten_id'=>$garden,'status'=>'open']);
    }

    public function test_graduated_child_without_active_group_is_not_reported_as_missing_group(): void
    {
        [$garden] = $this->structure();
        $child = DB::table('kindergarteners')->insertGetId([
            'municipality_id'=>DB::table('kindergartens')->where('id',$garden)->value('municipality_id'),
            'kindergarten_id'=>$garden,'group_id'=>null,'active_status_id'=>3,'application_status'=>'graduated',
            'graduate'=>true,'birth_date'=>'2020-01-10','kids_personal_number'=>'01001002004',
            'kids_first_name'=>'დასრულებული','kids_last_name'=>'ბავშვი','mobile_number'=>'551120240',
            'created_at'=>now(),'updated_at'=>now(),
        ]);

        app(DataQualityScanner::class)->run('scheduled');

        $this->assertDatabaseMissing('data_quality_issues', ['type'=>'invalid_group','entity_id'=>$child,'status'=>'open']);
    }

    private function structure(): array
    {
        $region = DB::table('regions')->insertGetId(['name' => 'Test']);
        $municipality = DB::table('municipalities')->insertGetId(['name' => 'Test', 'region_id' => $region]);
        $garden = DB::table('kindergartens')->insertGetId(['name' => 'Test', 'municipality_id' => $municipality]);
        $group = DB::table('group_age_ranges')->insertGetId(['range' => '2-3']);
        DB::table('kindergarten_group_age_range')->insert(['kindergarten_id' => $garden, 'group_age_range' => $group,
            'space_length' => 10, 'space_filled' => 0, 'space_free' => 10, 'space_reserved' => 0]);
        Setting::create(['slug' => 'date', 'object' => ['start' => '2025-09-15', 'end' => '2026-06-30']]);
        return [$garden, $group];
    }
}
