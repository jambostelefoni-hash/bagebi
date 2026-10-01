<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DataQualityScan extends Model
{
    protected $fillable = ['triggered_by', 'source', 'status', 'issues_found', 'critical_count',
        'warning_count', 'info_count', 'started_at', 'finished_at', 'error'];

    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime'];
}
