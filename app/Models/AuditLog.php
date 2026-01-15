<?php

namespace App\Models;

use App\Enums\AuditLogAction;
use App\Enums\AuditLogResult;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_election',
        'action',
        'result',
        'ip_address',
        'meta',
    ];

    protected $casts = [
        'action' => AuditLogAction::class,
        'result' => AuditLogResult::class,
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function election()
    {
        return $this->belongsTo(Election::class, 'id_election');
    }
}
