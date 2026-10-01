<?php

namespace Tests\Feature;

use App\Http\Controllers\SmsOfficeCallbackController;
use App\Model\AuditLog;
use App\Model\NotificationDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsOfficeCallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivered_callback_updates_delivery_and_creates_audit_entry(): void
    {
        $delivery = NotificationDelivery::create([
            'event' => 'application_created',
            'channel' => 'sms',
            'recipient_hash' => hash('sha256', '995551120240'),
            'idempotency_key' => 'callback-test-delivered',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->get('/api/sms-office/callback/'.SmsOfficeCallbackController::callbackToken().'?'.http_build_query([
            'reference' => 'sms-'.$delivery->id,
            'status' => 'Delivered',
            'reason' => '',
            'destination' => '995551120240',
            'timestamp' => '20260920191029',
        ]));

        $response->assertOk()->assertSeeText('OK');
        $this->assertSame('Delivered', $delivery->fresh()->provider_status);
        $this->assertNotNull($delivery->fresh()->delivered_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'notification.delivery_status',
            'model_id' => $delivery->id,
        ]);
    }

    public function test_callback_rejects_invalid_token_and_wrong_destination(): void
    {
        $delivery = NotificationDelivery::create([
            'event' => 'application_created',
            'channel' => 'sms',
            'recipient_hash' => hash('sha256', '995551120240'),
            'idempotency_key' => 'callback-test-protection',
            'status' => 'sent',
        ]);
        $query = http_build_query(['reference'=>'sms-'.$delivery->id,'status'=>'Delivered','destination'=>'995555000000']);

        $this->get('/api/sms-office/callback/wrong-token?'.$query)->assertNotFound();
        $this->get('/api/sms-office/callback/'.SmsOfficeCallbackController::callbackToken().'?'.$query)->assertNotFound();
        $this->assertNull($delivery->fresh()->provider_status);
        $this->assertSame(0, AuditLog::where('action', 'notification.delivery_status')->count());
    }
}
