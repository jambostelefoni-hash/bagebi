<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PublicPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'body',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];
}
