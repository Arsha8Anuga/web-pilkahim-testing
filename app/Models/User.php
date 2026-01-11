<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $table = 'users';

    protected $fillable = [
        'nim',
        'name',
        'password',
        'id_class',
        'role',
        'can_vote',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        // 'remember_token',
    ];

    

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            // 'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_vote' => 'boolean',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'deleted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function userClass(){
        return $this->belongsTo(UserClass::class, 'id_class');
    }

    public function cadidateChairpersons(){
        return $this->hasMany(Candidate::class, 'id_ketua');
    }

    public function candidateViceChairpersons(){
        return $this->hasMany(Candidate::class, 'id_wakil');
    }

    public function votes() {
        return $this->hasMany(Vote::class, 'id_user');
    }

    public function auditLogs() {
        return $this->hasMany(AuditLog::class, 'id_user');
    }
    
    public function getNameAttribute($value) {
        return ucfirst($value);
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = trim($value);
    }


    public function isAdmin(): bool {
        return $this->role === UserRole::ADMIN;
    }

    public function isVoter(): bool {
        return $this->role === UserRole::VOTER;
    }

    public function isActive(): bool {
        return $this->status === UserStatus::AKTIF;
    }

    public function hasVoted(): bool {
        return $this->votes()->exists();
    }

}
