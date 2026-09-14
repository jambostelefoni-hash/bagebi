<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
class NotificationDelivery extends Model
{
    protected $fillable = ['kindergartener_id','event','channel','recipient_hash','idempotency_key','status','attempts','last_error','sent_at'];
    protected $casts = ['sent_at'=>'datetime'];
    public function kindergartener(){return $this->belongsTo(\App\Model\API\Kindergartener::class);}
}
