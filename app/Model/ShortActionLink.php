<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ShortActionLink extends Model
{
    protected $fillable = ['code_hash', 'destination_url', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];
}
