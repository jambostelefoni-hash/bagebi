<?php

namespace Tests\Feature;

use App\Model\Setting;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_union_administrator_pages_render_without_server_errors(): void
    {
        $region = DB::table('regions')->insertGetId([
            'name' => 'Test region', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $municipality = DB::table('municipalities')->insertGetId([
            'region_id' => $region, 'name' => 'Test municipality', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $garden = DB::table('kindergartens')->insertGetId([
            'municipality_id' => $municipality, 'name' => 'Test garden', 'created_at' => now(), 'updated_at' => now(),
        ]);

        Setting::create(['slug' => 'date', 'object' => ['start' => '2026-09-15', 'end' => '2027-06-15']]);
        Setting::create(['slug' => 'basic', 'object' => [
            'isRegistrationStart' => true,
            'isPrioritetiesStart' => true,
            'isLearningStart' => false,
            'canPorting' => false,
        ]]);

        $admin = User::create([
            'name' => 'Test administrator',
            'email' => 'admin@example.test',
            'password' => Hash::make('testing-password'),
            'role' => 'union_admin',
            'kindergarten_id' => $garden,
        ]);

        $pages = [
            route('home'),
            route('structure.index'),
            route('control-center.index'),
            route('users.list'),
            route('users.create'),
            route('users.show', $admin->id),
            route('regions.list'),
            route('regions.show'),
            route('municipalities.list'),
            route('municipalities.show'),
            route('prioriteties.list'),
            route('prioriteties.show'),
            route('kindergartens.list'),
            route('kindergartens.show', $garden),
            route('kindergarteners.index'),
            route('kindergarteners.show'),
            route('settings.index'),
            route('settings.date'),
            route('registration-texts.index'),
            route('registration-texts.rules'),
            route('public-pages.index'),
            route('public-pages.edit', 'home'),
            route('audit-logs.index'),
            route('attendance.index', ['kindergarten_id' => $garden]),
            route('reinstatement.index'),
            route('calendar.index'),
            route('analytics.registration'),
            route('system-health.index'),
            route('data-quality.index'),
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }

    public function test_learning_start_action_is_hidden_after_the_year_has_started(): void
    {
        Setting::create(['slug' => 'date', 'object' => [
            'start' => today()->subDay()->toDateString(),
            'end' => today()->addMonths(9)->toDateString(),
        ]]);
        Setting::create(['slug' => 'basic', 'object' => [
            'isLearningStart' => true,
            'canPorting' => false,
        ]]);

        $admin = User::create([
            'name' => 'Test administrator',
            'email' => 'started-year@example.test',
            'password' => Hash::make('testing-password'),
            'role' => 'union_admin',
        ]);

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertDontSee(route('settings.learningStart'), false)
            ->assertSee('დაწყებულია');
    }

    public function test_year_actions_are_locked_and_porting_is_available_after_the_year_has_ended(): void
    {
        Setting::create(['slug' => 'date', 'object' => [
            'start' => today()->subMonths(9)->toDateString(),
            'end' => today()->subDay()->toDateString(),
        ]]);
        Setting::create(['slug' => 'basic', 'object' => [
            'isLearningStart' => false,
            'canPorting' => true,
        ]]);

        $admin = User::create([
            'name' => 'Test administrator',
            'email' => 'ended-year@example.test',
            'password' => Hash::make('testing-password'),
            'role' => 'union_admin',
        ]);

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertDontSee(route('settings.learningStart'), false)
            ->assertDontSee(route('settings.learningEnd'), false)
            ->assertSee('დასრულებულია')
            ->assertSee('data-submit="portireba"', false)
            ->assertDontSee('disabled data-submit="portireba"', false);
    }
}
