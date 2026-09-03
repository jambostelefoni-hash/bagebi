<?php

namespace App\Model;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
      'name'
    ];

    public function getCreatedAtAttribute ($date) {
        return Carbon::parse($date)->format('m/d/y');
    }
    
    //
    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }
}


