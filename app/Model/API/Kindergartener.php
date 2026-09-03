<?php

namespace App\Model\API;

use Illuminate\Database\Eloquent\Model;

class Kindergartener extends Model
{
    protected $table = 'kindergarteners';

    protected $fillable = [
        'kids_personal_number',
        'kids_first_name',
        'kids_last_name',
        'birth_date',
        'mother_personal_number',
        'mother_first_name',
        'mother_last_name',
        'father_personal_number',
        'father_first_name',
        'father_last_name',
        'mobile_number',
        'email',
        'municipality_id',
        'kindergarten_id',
        'group_id',
        'active_status_id'
    ];
protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
];

    // --- SCOPES ---
    public function scopeActive($query)
    {
        // აქ შეგიძლია შეცვალო სტატუსის მნიშვნელობა შენი ლოგიკით
        return $query->where('active_status_id', 1);
    }

    // --- ურთიერთობები ---
    public function priority()
    {
        return $this->hasOne(\App\Model\KindergartnerPriority::class, 'kindergartner_id', 'id');
    }

    public function municipality()
    {
        return $this->belongsTo(\App\Model\Municipality::class);
    }

    public function kindergarten()
    {
        return $this->belongsTo(\App\Model\Kindergarten::class);
    }

    public function groupRange()
    {
        return $this->belongsTo(\App\Model\GroupAgeRange::class, 'group_id');
    }

    public function activeStatus()
    {
        return $this->belongsTo(\App\Model\ActiveStatus::class, 'active_status_id');
    }
}
