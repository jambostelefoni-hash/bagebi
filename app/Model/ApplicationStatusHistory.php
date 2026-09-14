<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    protected $fillable = ['kindergartener_id', 'from_status', 'to_status', 'changed_by', 'reason'];
}
