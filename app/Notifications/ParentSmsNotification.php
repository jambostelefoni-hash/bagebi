<?php

namespace App\Notifications;

use App\Model\NotificationDelivery;
use App\Notifications\Channels\SmsOfficeChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ParentSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $messageLine, public int $deliveryId) {}

    public function via($notifiable): array
    {
        return [SmsOfficeChannel::class];
    }

    public function shouldSend($notifiable, string $channel): bool
    {
        return NotificationDelivery::whereKey($this->deliveryId)
            ->where(function ($query) {
                $query->whereIn('status', ['queued', 'failed'])
                    ->orWhere(function ($stale) {
                        $stale->where('status', 'sending')->where('updated_at', '<', now()->subMinutes(15));
                    });
            })
            ->update(['status' => 'sending']) === 1;
    }

    public function toSmsOffice($notifiable): array
    {
        return ['message' => $this->messageLine, 'reference' => 'sms-'.$this->deliveryId];
    }
}
