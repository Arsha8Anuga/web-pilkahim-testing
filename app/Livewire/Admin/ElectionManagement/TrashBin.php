<?php

namespace App\Livewire\Admin\Election;

use App\Concerns\WithAudit;
use App\DTO\Election\RestoreElectionDTO;
use App\DTO\Election\ForceDeleteElectionDTO;
use App\Enums\AuditLogAction;
use App\Models\Election;
use App\Service\Election\ElectionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class TrashBin extends Component
{
    use WithPagination, WithAudit;

    #[Title('Election Trash Bin')]

    protected string $paginationTheme = 'tailwind';

    public int $perPage = 10;

    public string $search = '';
    public string $sortBy = 'deleted_at';
    public string $sortDirection = 'desc';

    protected array $sortable = [
        'name',
        'deleted_at',
        'created_at',
    ];

    public array $modals = [
        'detail' => false,
        'restore' => false,
        'forceDelete' => false,
    ];

    public ?Election $modalElection = null;

    public function openModal(string $type, int $id): void {
        try {
            $this->modalElection = Election::onlyTrashed()->findOrFail($id);
            $this->modals[$type] = true;

        } catch (ModelNotFoundException) {
            Toaster::error('Election tidak ditemukan');
        }
    }

    public function closeModal(string $type): void {
        $this->modals[$type] = false;
        $this->modalElection = null;
    }

    public function restore(ElectionService $service): void {
        try {
            $this->withAudit(
                AuditLogAction::ELECTION_RESTORE,
                function () use ($service) {

                    if (! $this->modalElection) {
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('restore', $this->modalElection);

                    $dto = new RestoreElectionDTO($this->modalElection->id);
                    $service->restore($dto->id);
                },
                [
                    'election_id' => $this->modalElection?->id,
                ]
            );

            Toaster::success('Election berhasil direstore');
            $this->closeModal('restore');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('Election tidak ditemukan');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }

    public function forceDelete(ElectionService $service): void {
        try {
            $this->withAudit(
                AuditLogAction::ELECTION_FORCE_DELETE,
                function () use ($service) {

                    if (! $this->modalElection) {
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('forceDelete', $this->modalElection);

                    $dto = new ForceDeleteElectionDTO($this->modalElection->id);
                    $service->forceDelete($dto->id);
                },
                [
                    'election_id' => $this->modalElection?->id,
                ]
            );

            Toaster::success('Election berhasil dihapus permanen');
            $this->closeModal('forceDelete');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('Election tidak ditemukan');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }

    public function sort(string $field): void {
        if (! in_array($field, $this->sortable, true)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void {
        $this->resetPage();
    }


    public function render() {
        $elections = Election::onlyTrashed()
            ->select('id', 'name', 'status', 'deleted_at', 'created_at')
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.election-management.trash-bin', [
            'elections' => $elections,
        ]);
    }
}
