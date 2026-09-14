<?php
namespace App\Services;
use App\Model\API\Kindergartener;
use App\Model\NotificationDelivery;
use App\Notifications\ParentActionNotification;
use Illuminate\Support\Facades\Notification;
class ParentNotificationService
{
    public function send(Kindergartener $child, string $event, string $subject, string $message, ?string $actionUrl = null): void
    {
        if (!$child->email) return;
        $key=hash('sha256',$event.'|'.$child->id.'|'.($actionUrl ?: $child->status_changed_at));
        $delivery=NotificationDelivery::firstOrCreate(['idempotency_key'=>$key],['kindergartener_id'=>$child->id,'event'=>$event,'channel'=>'mail','recipient_hash'=>hash('sha256',strtolower($child->email)),'status'=>'queued']);
        if (!$delivery->wasRecentlyCreated) return;
        Notification::route('mail',$child->email)->notify(new ParentActionNotification($subject,$message,$actionUrl,$delivery->id));
    }
}
