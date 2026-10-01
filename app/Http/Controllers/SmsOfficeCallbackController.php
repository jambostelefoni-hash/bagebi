<?php

namespace App\Http\Controllers;

use App\Model\AuditLog;
use App\Model\NotificationDelivery;
use App\Services\SmsOfficeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SmsOfficeCallbackController extends Controller
{
    public function __invoke(Request $request, string $token, SmsOfficeService $smsOffice)
    {
        abort_unless(hash_equals($this->token(), $token), 404);

        $data = $request->validate([
            'reference' => ['required', 'string', 'regex:/^sms-([0-9]+)$/'],
            'status' => ['required', 'string', 'in:Delivered,delivered,Undelivered,undelivered,Expired,expired,Pending,pending,Unknown,unknown'],
            'reason' => ['nullable', 'string', 'max:500'],
            'destination' => ['required', 'string', 'max:20'],
            'timestamp' => ['nullable', 'string', 'max:14'],
        ]);

        preg_match('/^sms-([0-9]+)$/', $data['reference'], $matches);
        $delivery = NotificationDelivery::whereKey((int) $matches[1])->where('channel', 'sms')->firstOrFail();
        $recipientHash = hash('sha256', $smsOffice->normalizeMobile($data['destination']));
        abort_unless(hash_equals((string) $delivery->recipient_hash, $recipientHash), 404);

        $status = ucfirst(strtolower($data['status']));
        $oldStatus = $delivery->provider_status;
        $deliveredAt = $delivery->delivered_at;
        if ($status === 'Delivered') {
            $deliveredAt = $this->deliveredAt($data['timestamp'] ?? null) ?: now();
        }

        $delivery->update([
            'provider_status' => $status,
            'provider_reason' => $data['reason'] ?? null,
            'provider_updated_at' => now(),
            'delivered_at' => $deliveredAt,
            'status' => in_array($status, ['Undelivered', 'Expired'], true) ? 'failed' : $delivery->status,
            'last_error' => in_array($status, ['Undelivered', 'Expired'], true)
                ? (($data['reason'] ?? null) ?: 'SMS ადრესატს ვერ მიეწოდა.')
                : null,
        ]);

        if ($oldStatus !== $status) {
            AuditLog::create([
                'action' => 'notification.delivery_status',
                'model_type' => NotificationDelivery::class,
                'model_id' => $delivery->id,
                'description' => 'SMS მიწოდების სტატუსი განახლდა',
                'changes' => [
                    'provider_status' => ['old' => $oldStatus, 'new' => $status],
                    'reason' => $data['reason'] ?? null,
                ],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    public static function callbackToken(): string
    {
        return hash_hmac('sha256', 'smsoffice-callback', (string) config('app.key'));
    }

    private function token(): string
    {
        return self::callbackToken();
    }

    private function deliveredAt(?string $timestamp): ?Carbon
    {
        if (!$timestamp || !preg_match('/^\d{14}$/', $timestamp)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('YmdHis', $timestamp, config('app.timezone'));
        } catch (\Throwable $e) {
            return null;
        }
    }
}
