<?php

namespace App\Model;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{

    protected $fillable = [
      'name',
      'region_id'
    ];

    public function getCreatedAtAttribute ($date) {
        return Carbon::parse($date)->format('m/d/y');
    }

    //
    public function kindergartens()
    {
        return $this->hasMany(Kindergarten::class);
    }
    
    public function region()
    {
       return $this->belongsTo(Region::class, 'region_id', 'id');
    }
}





