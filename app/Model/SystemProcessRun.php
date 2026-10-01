<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SystemProcessRun extends Model
{
    protected $fillable = ['process', 'status', 'started_at', 'finished_at', 'error'];
    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime'];
}
