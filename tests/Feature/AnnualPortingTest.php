<?php

namespace Tests\Feature;

use App\Model\API\Kindergartener;
use App\Model\Setting;
use App\Services\AnnualPortingService;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AnnualPortingTest extends TestCase
{
    use RefreshDatabase;

    protected $garden;
    protected $municipality;
    protected $serial = 0;

    protected function beforeRefreshingDatabase()
    {
        if (config('database.connections.'.config('database.default').'.database') !== 'bagebi_testing') {
            throw new \RuntimeException('This test requires the isolated bagebi_testing database.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-07-01 12:00:00');
        $region = DB::table('regions')->insertGetId(['name' => 'Test']);
        $this->municipality = DB::table('municipalities')->insertGetId(['name' => 'Test', 'region_id' => $region]);
        $this->garden = DB::table('kindergartens')->insertGetId(['name' => 'Test', 'municipality_id' => $this->municipality]);
        DB::table('group_age_ranges')->delete();
        foreach ([10 => '2-3', 30 => '3-4', 50 => '4-5', 70 => '5-6'] as $id => $range) {
            DB::table('group_age_ranges')->insert(['id' => $id, 'range' => $range]);
            DB::table('kindergarten_group_age_range')->insert(['kindergarten_id' => $this->garden,
                'group_age_range' => $id, 'space_length' => 5, 'space_filled' => 0, 'space_free' => 5, 'space_reserved' => 0]);
        }
        Setting::create(['slug' => 'date', 'object' => ['start' => '09/15/2025', 'end' => '06/30/2026']]);
        Setting::create(['slug' => 'basic', 'object' => ['canPorting' => true, 'isLearningStart' => false]]);
        $this->actingAs(User::create(['name' => 'Test admin', 'email' => 'porting@example.test',
            'password' => bcrypt('test-password'), 'role' => 'union_admin']));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function child(int $group, string $status = 'enrolled'): Kindergartener
    {
        return Kindergartener::create(['kindergarten_id' => $this->garden, 'municipality_id' => $this->municipality,
            'group_id' => $group, 'application_status' => $status, 'kids_first_name' => 'Test',
            'kids_last_name' => 'Child', 'kids_personal_number' => sprintf('0900000%04d', ++$this->serial),
            'mobile_number' => '555000000']);
    }

    private function rejected(): void
    {
        try {
            app(AnnualPortingService::class)->execute();
            $this->fail('Porting should have been rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('porting', $exception->errors());
        }
    }

    public function test_porting_preserves_limits_statuses_queue_priority_and_audits_every_child(): void
    {
        $enrolled = $this->child(10);
        $suspended = $this->child(10, 'suspended');
        $waiting = $this->child(10, 'waiting');
        $graduate = $this->child(70);
        $cancelled = $this->child(10, 'cancelled');
        DB::table('waiting_list_entries')->insert(['kindergartener_id' => $waiting->id,
            'kindergarten_id' => $this->garden, 'group_id' => 10, 'state' => 'waiting',
            'priority_rank' => 2, 'queued_at' => '2025-09-01 09:00:00']);

        $this->assertSame(['moved' => 3, 'graduated' => 1], app(AnnualPortingService::class)->execute());
        $this->assertEquals(30, $enrolled->fresh()->group_id);
        $this->assertSame('suspended', $suspended->fresh()->application_status);
        $this->assertEquals(10, $cancelled->fresh()->group_id);
        $this->assertSame('graduated', $graduate->fresh()->application_status);
        $this->assertNull($graduate->fresh()->group_id);
        $this->assertDatabaseHas('waiting_list_entries', ['kindergartener_id' => $waiting->id,
            'group_id' => 30, 'priority_rank' => 2, 'queued_at' => '2025-09-01 09:00:00']);
        $this->assertDatabaseHas('kindergarten_group_age_range', ['kindergarten_id' => $this->garden,
            'group_age_range' => 30, 'space_length' => 5, 'space_filled' => 2, 'space_free' => 3]);
        $this->assertDatabaseHas('kindergarten_group_age_range', ['kindergarten_id' => $this->garden,
            'group_age_range' => 10, 'space_length' => 5, 'space_filled' => 0, 'space_free' => 5]);
        $this->assertEquals(20, DB::table('kindergarten_group_age_range')->sum('space_length'));
        $this->assertEquals(4, DB::table('audit_logs')->where('action', 'kindergartener.ported')->count());
        $this->assertSame('06/30/2026', data_get(Setting::where('slug', 'date')->first(), 'object.end'));
    }

    public function test_insufficient_capacity_rolls_back_all_children_and_limits(): void
    {
        $child = $this->child(10);
        $this->child(10, 'suspended');
        DB::table('kindergarten_group_age_range')->where('group_age_range', 30)->update(['space_length' => 1]);
        $this->rejected();
        $this->assertEquals(10, $child->fresh()->group_id);
        $this->assertDatabaseCount('audit_logs', 0);
        $this->assertTrue(data_get(Setting::where('slug', 'basic')->first(), 'object.canPorting'));
        $this->assertEquals(1, DB::table('kindergarten_group_age_range')->where('group_age_range', 30)->value('space_length'));
    }

    public function test_missing_target_capacity_is_rejected(): void
    {
        $child = $this->child(10);
        DB::table('kindergarten_group_age_range')->where('group_age_range', 30)->delete();
        $this->rejected();
        $this->assertEquals(10, $child->fresh()->group_id);
    }

    public function test_same_year_cannot_be_ported_again_even_if_button_is_reenabled(): void
    {
        $child = $this->child(10);
        app(AnnualPortingService::class)->execute();
        $basic = Setting::where('slug', 'basic')->first();
        $basic->update(['object' => array_merge($basic->object, ['canPorting' => true])]);
        $this->rejected();
        $this->assertEquals(30, $child->fresh()->group_id);
    }

    public function test_unresolved_offer_blocks_porting(): void
    {
        $child = $this->child(10, 'waiting');
        DB::table('waiting_list_entries')->insert(['kindergartener_id' => $child->id,
            'kindergarten_id' => $this->garden, 'group_id' => 10, 'state' => 'offered', 'queued_at' => now()]);
        $this->rejected();
        $this->assertEquals(10, $child->fresh()->group_id);
    }

    public function test_future_year_end_blocks_porting(): void
    {
        Setting::where('slug', 'date')->first()->update(['object' => ['end' => '2027-06-30']]);
        $this->rejected();
    }

    public function test_custom_unmapped_group_is_not_silently_incremented(): void
    {
        DB::table('group_age_ranges')->where('id', 10)->update(['range' => 'Mixed group']);
        $child = $this->child(10);
        $this->rejected();
        $this->assertEquals(10, $child->fresh()->group_id);
    }

    public function test_occupied_group_cannot_be_saved_with_zero_capacity(): void
    {
        $this->child(10);
        $this->post(route('kindergartens.store'), ['id' => $this->garden, 'name' => 'Test',
            'municipality_id' => $this->municipality, 'range' => [10 => ['space_length' => 0]]])
            ->assertSessionHasErrors('range');
        $this->assertEquals(5, DB::table('kindergarten_group_age_range')->where('group_age_range', 10)->value('space_length'));
    }

    public function test_omitted_groups_are_preserved_when_capacity_is_saved(): void
    {
        $this->post(route('kindergartens.store'), ['id' => $this->garden, 'name' => 'Test',
            'municipality_id' => $this->municipality, 'range' => [10 => ['space_length' => 7]]])->assertRedirect(route('kindergartens.list'));
        $this->assertDatabaseHas('kindergarten_group_age_range', ['group_age_range' => 30, 'space_length' => 5]);
        $this->assertEquals(7, DB::table('kindergarten_group_age_range')->where('group_age_range', 10)->value('space_length'));
    }

    public function test_garden_list_counts_occupied_children_without_a_pivot(): void
    {
        $this->child(10);
        $this->child(10, 'suspended');
        DB::table('kindergarten_group_age_range')->where('group_age_range', 10)->delete();
        $this->get(route('kindergartens.list'))->assertOk()->assertViewHas('model', fn ($gardens) => (int) $gardens->first()->occupied_children_count === 2);
    }
}
