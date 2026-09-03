<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RegistrationText extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'rules'
    ];
}
