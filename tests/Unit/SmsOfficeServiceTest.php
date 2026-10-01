<?php

namespace Tests\Unit;

use App\Services\SmsOfficeService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SmsOfficeServiceTest extends TestCase
{
    public function test_it_normalizes_georgian_mobile_numbers(): void
    {
        $service = app(SmsOfficeService::class);

        $this->assertSame('995555123456', $service->normalizeMobile('555 123 456'));
        $this->assertSame('995555123456', $service->normalizeMobile('+995 555 123 456'));
        $this->assertSame('995555123456', $service->normalizeMobile('0555 123 456'));
        $this->assertSame('995555123456', $service->normalizeMobile('00995 555 123 456'));
    }

    public function test_it_rejects_invalid_mobile_numbers(): void
    {
        $this->expectException(RuntimeException::class);
        app(SmsOfficeService::class)->normalizeMobile('12345');
    }

    public function test_it_checks_delivery_status_by_reference(): void
    {
        config(['services.smsoffice.api_key' => 'test-key']);
        Http::fake([
            'smsoffice.ge/api/v2/getMessageStatus/*' => Http::response([
                'Success' => true,
                'Message' => 'found',
                'Output' => ['Status' => 'Delivered'],
                'ErrorCode' => 0,
            ]),
        ]);

        $result = app(SmsOfficeService::class)->status('551120240', 'sms-test-123456');

        $this->assertSame('Delivered', data_get($result, 'Output.Status'));
        Http::assertSent(fn ($request) =>
            $request->url() === 'https://smsoffice.ge/api/v2/getMessageStatus/?key=test-key&destination=995551120240&reference=sms-test-123456'
        );
    }
}
