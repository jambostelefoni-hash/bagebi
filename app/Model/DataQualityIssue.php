<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DataQualityIssue extends Model
{
    protected $fillable = ['fingerprint', 'type', 'severity', 'status', 'entity_type', 'entity_id',
        'kindergarten_id', 'message', 'details', 'first_detected_at', 'last_detected_at', 'resolved_at'];

    protected $casts = ['details' => 'array', 'first_detected_at' => 'datetime',
        'last_detected_at' => 'datetime', 'resolved_at' => 'datetime'];

    public function kindergarten()
    {
        return $this->belongsTo(Kindergarten::class);
    }
}
