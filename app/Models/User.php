<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_vote' => 'boolean',
        ];
    }

    public function userClass() {
        return $this->belongsTo(UserClass::class, 'id_class');
    }

    public function kandidatKetua() {
        return $this->hasMany(Candidate::class, 'id_ketua');
    }

    public function kandidatWakil() {
        return $this->hasMany(Candidate::class, 'id_wakil');
    }

    public function userVote() {
        return $this->hasMany(Vote::class, 'id_user');
    }

    public function userLog() {
        return $this->hasMany(AuditLog::class, 'id_user');
    }

}
