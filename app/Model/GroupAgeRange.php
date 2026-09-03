<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroupAgeRange extends Model
{
    public function Kindergartens()
    {
        return $this->belongsToMany(Kindergarten::class);
    }
}
