<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
class NotificationDelivery extends Model
{
    protected $fillable = ['kindergartener_id','event','channel','recipient_hash','payload','idempotency_key','status','provider_status','provider_reason','provider_updated_at','delivered_at','attempts','last_error','sent_at'];
    protected $casts = ['sent_at'=>'datetime','provider_updated_at'=>'datetime','delivered_at'=>'datetime','payload'=>'array'];
    public function kindergartener(){return $this->belongsTo(\App\Model\API\Kindergartener::class);}
}
