<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReinstatementRequest extends Model
{
    protected $fillable = ['kindergartener_id', 'token_hash', 'document_path', 'original_filename', 'status', 'expires_at', 'absence_from', 'absence_to', 'reviewed_by', 'reviewed_at', 'review_note'];
    protected $casts = ['expires_at' => 'datetime', 'reviewed_at' => 'datetime', 'absence_from'=>'date', 'absence_to'=>'date'];
    public function kindergartener(){return $this->belongsTo(\App\Model\API\Kindergartener::class);}
}
