<?php
namespace App\Listeners;
use App\Model\NotificationDelivery;
use Illuminate\Notifications\Events\NotificationFailed;
class MarkNotificationDeliveryFailed
{
    public function handle(NotificationFailed $event){$id=$event->notification->deliveryId??null;if($id){$error=(string)($event->data['exception']??'Delivery failed');$error=trim(explode("\n",$error)[0]);$error=preg_replace('/^[\\\\\w]+Exception:\s*/','',$error);$normalized=strtolower($error);if(str_contains($normalized,'sender is not active')||str_contains($normalized,'invalid sender'))$error='SMS Office Sender არ არის აქტიური ან არასწორად არის მითითებული.';NotificationDelivery::whereKey($id)->where('status','sending')->update(['status'=>'failed','attempts'=>\DB::raw('attempts + 1'),'last_error'=>mb_substr($error,0,300)]);}}
}
