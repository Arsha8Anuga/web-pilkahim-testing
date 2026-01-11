<?php

namespace App\Models;

use App\Enums\ElectionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Election extends Model
{

    use SoftDeletes;

    protected $table = 'elections';

    protected $fillable = [
        'name',
        'description',
        'voting_start',
        'voting_end',
        'status'
    ];

    protected $casts = [
        'status' => ElectionStatus::class,
        'voting_start' => 'datetime',
        'voting_end' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'id_election');
    }

    public function votes() {
        return $this->hasMany(Vote::class, 'id_election');
    }

    public function auditLogs() {
        return $this->hasMany(AuditLog::class, 'id_election');
    }

    public function getNameAttribute($value) {
        return ucfirst($value);
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = trim($value);
    }

    public function isOpen(): bool {
        return $this->status === ElectionStatus::DIBUKA;
    }

    public function isClosed(): bool {
        return $this->status === ElectionStatus::DITUTUP;
    }

}
