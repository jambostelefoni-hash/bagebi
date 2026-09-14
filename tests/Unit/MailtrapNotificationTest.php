<?php

namespace Tests\Unit;

use App\Notifications\Channels\MailtrapEmailChannel;
use App\Notifications\ParentActionNotification;
use Tests\TestCase;

class MailtrapNotificationTest extends TestCase
{
    public function test_parent_notification_uses_mailtrap_channel_when_api_token_is_configured(): void
    {
        config(['services.mailtrap.api_token' => 'test-token']);
        $notification = new ParentActionNotification('სათაური', 'ტექსტი', 'https://example.test/action');

        $this->assertSame([MailtrapEmailChannel::class], $notification->via(null));
        $this->assertSame([
            'subject' => 'სათაური',
            'message' => 'ტექსტი',
            'action_url' => 'https://example.test/action',
            'category' => 'Kindergarten platform',
        ], $notification->toMailtrap(null));
    }
}
