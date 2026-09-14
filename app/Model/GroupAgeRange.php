<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroupAgeRange extends Model
{
    public $timestamps = false;
    protected $fillable = ['range'];

    public function kindergartens()
    {
        return $this->belongsToMany(
            Kindergarten::class,
            'kindergarten_group_age_range',
            'group_age_range',
            'kindergarten_id'
        );
    }
}
