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
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }
}
