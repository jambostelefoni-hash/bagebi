<?php

namespace App\Notifications\Channels;

use App\Services\MailtrapEmailService;
use Illuminate\Notifications\Notification;

class MailtrapEmailChannel
{
    private $mailtrap;

    public function __construct(MailtrapEmailService $mailtrap)
    {
        $this->mailtrap = $mailtrap;
    }

    public function send($notifiable, Notification $notification)
    {
        $recipient = $notifiable->routeNotificationFor('mail', $notification);
        if (is_array($recipient)) {
            $recipient = array_key_first($recipient);
        }

        if (!$recipient) {
            return null;
        }

        $message = $notification->toMailtrap($notifiable);

        return $this->mailtrap->send(
            $recipient,
            $message['subject'],
            $message['message'],
            $message['action_url'] ?? null,
            $message['category'] ?? 'Platform notification'
        );
    }
}
