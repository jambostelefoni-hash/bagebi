<?php

namespace Tests\Feature;

use App\Model\NotificationDelivery;
use App\Model\Setting;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SystemHealthNotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_later_successful_sms_resolves_an_older_failed_delivery_in_system_health(): void
    {
        Setting::create(['slug' => 'basic', 'object' => ['isRegistrationStart' => true]]);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'system-health@example.test',
            'password' => Hash::make('password'),
            'role' => 'union_admin',
        ]);

        $failed = NotificationDelivery::create([
            'kindergartener_id' => 42,
            'event' => 'status_suspended',
            'channel' => 'sms',
            'idempotency_key' => 'health-failed-delivery',
            'status' => 'failed',
            'last_error' => 'Invalid Georgian mobile number',
        ]);
        DB::table('notification_deliveries')->where('id', $failed->id)->update([
            'updated_at' => now()->subMinute(),
        ]);
        NotificationDelivery::create([
            'kindergartener_id' => 42,
            'event' => 'status_suspended',
            'channel' => 'sms',
            'idempotency_key' => 'health-successful-delivery',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('system-health.index'))
            ->assertOk()
            ->assertDontSee('1 შეტყობინება ვერ გაიგზავნა.')
            ->assertSee('მიწოდების რიგში შეფერხება არ არის.');
    }
}
