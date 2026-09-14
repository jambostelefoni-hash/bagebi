<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PlacementOffer extends Model
{
    protected $fillable = ['waiting_list_entry_id', 'token_hash', 'expires_at', 'responded_at', 'response'];
    protected $casts = ['expires_at' => 'datetime', 'responded_at' => 'datetime'];
    public function entry() { return $this->belongsTo(WaitingListEntry::class, 'waiting_list_entry_id'); }
}
