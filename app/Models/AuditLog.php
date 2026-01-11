<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{

    protected $table = 'audit_logs';

    protected $fillable = [
        'id_user',
        'id_election',
        'action',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];
    
    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function election() {
        return $this->belongsTo(Election::class, 'id_election');
    }

    
}
