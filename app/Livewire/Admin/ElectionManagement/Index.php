<?php

namespace App\Livewire\Admin\Election;

use App\Concerns\WithAudit;
use App\DTO\Election\CreateElectionDTO;
use App\DTO\Election\UpdateElectionDTO;
use App\Enums\AuditLogAction;
use App\Models\Election;
use App\Service\Election\ElectionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class Index extends Component
{
    use WithPagination,AuthorizesRequests, WithAudit;

    #[Title('Election Management')]

    protected string $paginationTheme = 'tailwind';

    public int $perPage = 10;

    public string $search = '';
    public string $sortBy = 'created_at';
    public ?string $filterStatus = null; 
    public string $sortDirection = 'desc';

    protected array $sortable = [
        'name',
        'status',
        'voting_start',
        'voting_end',
        'created_at',
    ];

    public array $modals = [
        'detail' => false,
        'create' => false,
        'update' => false,
        'delete' => false,
    ];

    public ?Election $modalElection = null;

    public function openModal(string $type, int $id = null): void {
        try {
            if (in_array($type, ['detail', 'update', 'delete'], true)) {
                $this->modalElection = Election::findOrFail($id);
            }

            $this->modals[$type] = true;

        } catch (ModelNotFoundException) {
            Toaster::error('Election tidak ditemukan');
        }
    }

    public function closeModal(string $type): void {
        $this->modals[$type] = false;
        $this->modalElection = null;
    }

    public function create(ElectionService $service): void {
        try {
            $this->withAudit(
                AuditLogAction::ELECTION_CREATE,
                function () use ($service) {

                    $this->authorize('create', Election::class);

                    $data = $this->validate(CreateElectionDTO::rules());
                    $dto  = CreateElectionDTO::from($data);

                    return $service->create($dto);
                }
            );

            Toaster::success('Election berhasil dibuat');
            $this->closeModal('create');

        } catch (ValidationException) {
            Toaster::error('Validasi gagal');
        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }

    public function update(ElectionService $service): void {
        try {
            $this->withAudit(
                AuditLogAction::ELECTION_UPDATE,
                function () use ($service) {

                    if (! $this->modalElection) {
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('update', $this->modalElection);

                    $data = $this->validate(UpdateElectionDTO::rules());
                    $dto  = UpdateElectionDTO::from($data);

                    $service->update($this->modalElection, $dto);
                },
                [
                    'election_id' => $this->modalElection?->id,
                ]
            );

            Toaster::success('Election berhasil diperbarui');
            $this->closeModal('update');

        } catch (ValidationException) {
            Toaster::error('Validasi gagal');
        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('Election tidak ditemukan');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }


    public function delete(ElectionService $service): void {
        try {
            $this->withAudit(
                AuditLogAction::ELECTION_DELETE,
                function () use ($service) {

                    if (! $this->modalElection) {
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('delete', $this->modalElection);

                    $service->delete($this->modalElection);
                },
                [
                    'election_id' => $this->modalElection?->id,
                ]
            );

            Toaster::success('Election berhasil dihapus');
            $this->closeModal('delete');

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

    public function updatedFilterStatus(): void {
        $this->resetPage();
    }

    public function render() {
        $elections = Election::query()
            ->select('id', 'name', 'status', 'voting_start', 'voting_end', 'created_at')
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->when($this->filterStatus, fn ($q) =>
                $q->where('status', $this->filterStatus)
            )
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.election-management.index', [
            'elections' => $elections,
        ]);
    }
}
