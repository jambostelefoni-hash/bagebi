<?php

namespace Tests\Feature;

use App\Model\AuditLog;
use App\Model\Setting;
use App\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditLogFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_union_admin_can_filter_logs_by_actor_and_director_cannot_open_journal(): void
    {
        Setting::create(['slug' => 'basic', 'object' => ['canPorting' => false]]);
        $admin = $this->user('Audit administrator', 'audit-admin@example.test', 'union_admin');
        $director = $this->user('Garden director', 'director@example.test', 'director');

        AuditLog::create(['user_id' => $admin->id, 'action' => 'settings.update', 'description' => 'Admin change', 'ip' => '10.0.0.1']);
        AuditLog::create(['user_id' => $director->id, 'action' => 'attendance.store', 'description' => 'Director attendance', 'ip' => '10.0.0.2']);

        $this->actingAs($admin)
            ->get(route('audit-logs.index', ['user_id' => $director->id]))
            ->assertOk()
            ->assertSee('Director attendance')
            ->assertDontSee('Admin change');

        $this->actingAs($director)->get(route('audit-logs.index'))->assertForbidden();
    }

    public function test_audit_entries_are_append_only(): void
    {
        $log = AuditLog::create(['action' => 'auth.login']);
        $this->expectException(\LogicException::class);
        $log->update(['action' => 'tampered']);
    }

    public function test_login_log_keeps_actor_snapshot(): void
    {
        $director = $this->user('Snapshot director', 'snapshot@example.test', 'director');
        event(new Login('web', $director, false));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
            'user_id' => $director->id,
            'actor_name' => 'Snapshot director',
            'actor_email' => 'snapshot@example.test',
            'actor_role' => 'director',
        ]);
    }

    private function user(string $name, string $email, string $role): User
    {
        return User::create(['name' => $name, 'email' => $email, 'password' => Hash::make('testing-password'), 'role' => $role]);
    }
}
