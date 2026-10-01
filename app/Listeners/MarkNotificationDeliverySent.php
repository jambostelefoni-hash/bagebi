<?php
namespace App\Listeners;
use App\Model\NotificationDelivery;
use Illuminate\Notifications\Events\NotificationSent;
class MarkNotificationDeliverySent
{
    public function handle(NotificationSent $event){$id=$event->notification->deliveryId??null;if($id)NotificationDelivery::whereKey($id)->where('status','sending')->update(['status'=>'sent','sent_at'=>now(),'attempts'=>\DB::raw('attempts + 1'),'last_error'=>null]);}
}
