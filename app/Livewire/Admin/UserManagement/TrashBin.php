<?php

namespace App\Livewire\Admin\UserManagement;

use App\Concerns\WithAudit;
use App\DTO\User\ForceDeleteUserDTO;
use App\DTO\User\RestoreUserDTO;
use App\Enums\AuditLogAction;
use App\Models\User;
use App\Service\User\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class TrashBin extends Component
{
    use WithPagination, AuthorizesRequests, WithAudit;

    #[Title('User Trash Bin')]

    public int $paginationIndex = 10;
    public string $currentState = '';
    public ?User $modalUser = null;

    public array $modals = [
        'restore' => false,
        'forceDelete' => false,
    ];

    protected array $sortable = [
        'name',
        'nim',
        'deleted_at',
        'created_at'
    ];

    protected string $paginationTheme = 'tailwind';
        
    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
 
    public function openModal(string $type, int $id)
    {
        try {
            $this->modalUser = User::onlyTrashed()->findOrFail($id);
            $this->currentState = $type;
            $this->modals[$type] = true;

        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        }
    }

    public function closeModal(string $type)
    {
        $this->modals[$type] = false;
        $this->currentState = '';
        $this->modalUser = null;
    }

    public function restore(UserService $service){
        try {

            $this->withAudit(
                AuditLogAction::USER_RESTORE,
                function () use ($service) {

                    if (! $this->modalUser) {
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('restore', $this->modalUser);

                    $dto = new RestoreUserDTO($this->modalUser->id);
                    $service->restore($dto);
                },
                [
                    'restored_user_id' => $this->modalUser?->id,
                ]
            );

            Toaster::success("User {$this->modalUser->name} berhasil dipulihkan");
            $this->closeModal('restore');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }


    public function forceDelete(UserService $service){
        try {

            $this->withAudit(
                AuditLogAction::USER_FORCE_DELETE,
                function () use ($service) {

                    if (! $this->modalUser) {
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('forceDelete', $this->modalUser);

                    $dto = new ForceDeleteUserDTO($this->modalUser->id);
                    $service->forceDelete($dto);
                },
                [
                    'force_deleted_user_id' => $this->modalUser?->id,
                ]
            );

            Toaster::success("User {$this->modalUser->name} dihapus permanen");
            $this->closeModal('forceDelete');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }

     public function updatePaginationIndex(){
        $this->resetPage();
    }

    public function updatedSearch(){
        $this->resetPage();
    }

    public function sort(string $field){

        if (! in_array($field, $this->sortable)) {
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

    public function render(){
        $users = User::onlyTrashed()
            ->select('id', 'nim', 'name', 'created_at', 'deleted_at')
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->search}%")
                    ->orWhere('nim', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->paginationIndex);

        return view('livewire.admin.user-management.trash-bin', [
            'users' => $users
        ]);
    }
}
