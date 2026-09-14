<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WaitingListEntry extends Model
{
    protected $fillable = ['kindergartener_id', 'kindergarten_id', 'group_id', 'priority_rank', 'queued_at', 'state'];
    protected $casts = ['queued_at' => 'datetime'];

    public function kindergartener() { return $this->belongsTo(\App\Model\API\Kindergartener::class); }
    public function kindergarten() { return $this->belongsTo(Kindergarten::class); }
    public function groupRange() { return $this->belongsTo(GroupAgeRange::class, 'group_id'); }
    public function offers() { return $this->hasMany(PlacementOffer::class); }
}
