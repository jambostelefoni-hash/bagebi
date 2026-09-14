<?php
namespace App\Listeners;
use App\Model\NotificationDelivery;
use Illuminate\Notifications\Events\NotificationFailed;
class MarkNotificationDeliveryFailed
{
    public function handle(NotificationFailed $event){$id=$event->notification->deliveryId??null;if($id)NotificationDelivery::whereKey($id)->update(['status'=>'failed','attempts'=>\DB::raw('attempts + 1'),'last_error'=>mb_substr((string)($event->data['exception']??'Delivery failed'),0,2000)]);}
}
