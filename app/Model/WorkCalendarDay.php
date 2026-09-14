<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WorkCalendarDay extends Model
{
    protected $fillable = ['date', 'kindergarten_id', 'is_working_day', 'label'];
    protected $casts = ['date' => 'date', 'is_working_day' => 'boolean'];
}
