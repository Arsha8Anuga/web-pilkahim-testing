<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $table = 'candidates';

    protected $fillable = [
        'id_election',
        'no_urut',
        'nama_pasangan',
        'id_ketua',
        'id_wakil',
        'visi',
        'misi',
        'foto_path'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function election() { 
        return $this->belongsTo(Election::class, 'id_election');
    }

    public function chairperson() {
        return $this->belongsTo(User::class, 'id_ketua');
    }

    public function viceChairperson() {
        return $this->belongsTo(User::class, 'id_wakil');
    }

    public function votes() {
        return $this->hasMany(Vote::class, "id_candidate");
    }

    public function getNamaPasanganAttribute($value) {
        return ucfirst($value);
    }

    public function setNamaPasanganAttribute($value) {
        $this->attributes['nama_pasangan'] = trim($value);
    }

    public function isInElection(Election $election): bool {
        return $this->id_election === $election->id;
    }

    public function voteCount(): int {
        return $this->votes()->count();
    }

    public function votePercentage(): float {
        $totalVotes = $this->election->votes()->count();
        return $totalVotes ? ($this->voteCount() / $totalVotes) * 100 : 0;
    }

    public function hasVotes(): bool {
        return $this->votes()->exists();
    }


    public function hasWon(): bool {
        if (!$this->election || !$this->election->isClosed()) {
            return false;
        }

        $votesCounts = $this->election->candidates()
            ->withCount('votes')
            ->pluck('votes_count', 'id');

        $maxVotes = $votesCounts->max();

        $topCandidates = $votesCounts->filter(fn($votes) => $votes === $maxVotes);

        return $topCandidates->count() === 1 && $topCandidates->keys()->first() === $this->id;
    }

}
