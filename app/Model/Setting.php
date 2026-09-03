<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    //

    protected $fillable = [
      'slug',
      'object'
    ];

    protected $casts = [
      'object' => 'array'
    ];

    protected $attributes = [
       'slug' => 'basic',
       'object' => '[]'
    ];

    // public function setObjectAttribute ($value) {
    // 	$this->attributes['object'] = json_encode($value);
    // }

    // public function getObjectAttribute ($value) {
    // 	return json_decode($value, true);
    // }
}


