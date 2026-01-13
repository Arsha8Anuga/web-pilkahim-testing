<?php

namespace App\Livewire\Admin;

use App\Enums\ElectionStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Election;
use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{   

    #[Title('Admin Dashboard')]
    
    public int $totalUsers = 0;
    public int $totalAdmin = 0;
    public int $totalVoter = 0;
    public int $totalPassiveVoter = 0;
    public int $totalActiveVoter = 0;
    public int $totalCanVote = 0;
    public $activeElection = null;
    public $electionCandidates = [];
    public $electionPercentage = [];



    public function mount() { 

        $this->totalUsers = User::count();

        $this->totalAdmin = User::where('role', UserRole::ADMIN)->count();

        $this->totalVoter = User::where('role', UserRole::VOTER)->count();

        $this->totalPassiveVoter = User::where('status', UserStatus::PASIF)->count();

        $this->totalActiveVoter = User::where('status', UserStatus::AKTIF)->count();

        $this->totalCanVote = User::where('can_vote', true)->count();
        
        $this->activeElection = Election::where('status', ElectionStatus::DIBUKA)
                                ->with(['candidates' => fn ($q) => $q->withCount('votes')])
                                ->first();
                       
        if ($this->activeElection) { 

            $this->electionCandidates = $this->activeElection->candidates;
            
            $totalVotes = $this->activeElection->votes()->count();

            foreach ($this->activeElection->candidates as $candidate) {
                $this->electionPercentage[$candidate->id] = $totalVotes > 0
                ? round(($candidate->votes_count / $totalVotes) * 100, 2)
                : 0;
            }
        }

    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
