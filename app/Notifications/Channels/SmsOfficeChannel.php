<?php

namespace App\Notifications\Channels;

use App\Services\SmsOfficeService;
use Illuminate\Notifications\Notification;

class SmsOfficeChannel
{
    public function __construct(private SmsOfficeService $smsOffice) {}

    public function send($notifiable, Notification $notification): ?array
    {
        $recipient = $notifiable->routeNotificationFor('sms', $notification);
        if (!$recipient) return null;

        $message = $notification->toSmsOffice($notifiable);
        return $this->smsOffice->send($recipient, $message['message'], $message['reference']);
    }
}
