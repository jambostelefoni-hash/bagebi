<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'actor_name',
        'actor_email',
        'actor_role',
        'action',
        'model_type',
        'model_id',
        'description',
        'changes',
        'ip',
        'user_agent'
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    protected static function booted()
    {
        static::updating(function () {
            throw new \LogicException('Audit log entries are append-only.');
        });

        static::deleting(function () {
            throw new \LogicException('Audit log entries cannot be deleted.');
        });
    }
}
