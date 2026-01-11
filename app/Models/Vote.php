<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{

    protected $table = 'votes';

    protected $fillable = [
        'id_election',
        'id_user',
        'id_candidate'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function election() {
        return $this->belongsTo(Election::class, 'id_election');
    }
    
    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'id_candidate');
    }

    public function isBy(User $user): bool {
        return $this->id_user === $user->id;
    }

    public function belongsToElection(Election $election): bool {
        return $this->id_election === $election->id;
    }

}
