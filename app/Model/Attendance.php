<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    const PRESENT = 'present';
    const ABSENT = 'absent';
    const EXCUSED = 'excused';
    const NON_WORKING = 'non_working';

    protected $fillable = ['kindergartener_id', 'kindergarten_id', 'group_id', 'attendance_date', 'status', 'recorded_by', 'note'];
    protected $casts = ['attendance_date' => 'date'];
    public function kindergartener() { return $this->belongsTo(\App\Model\API\Kindergartener::class); }

    public function getStatusLabelAttribute()
    {
        return config('statuses.attendance.'.$this->status, $this->status);
    }
}
