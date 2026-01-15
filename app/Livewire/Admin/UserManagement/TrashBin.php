<?php

namespace App\Livewire\Admin\UserManagement;

use App\DTO\User\ForceDeleteUserDTO;
use App\DTO\User\RestoreUserDTO;
use App\Models\User;
use App\Service\User\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class TrashBin extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Title('User Trash Bin')]

    public int $paginationIndex = 10;
    public string $currentState = '';
    public ?User $modalUser = null;

    public $modals = [
        'restore' => false,
        'forceDelete' => false,
    ];

    protected string $paginationTheme = 'tailwind';

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

    public function restore(UserService $service)
    {
        try {
            $this->authorize('restore', $this->modalUser);

            $dto = new RestoreUserDTO($this->modalUser->id);
            $service->restore($dto);

            Toaster::success("User {$this->modalUser->name} berhasil dipulihkan");
            $this->closeModal('restore');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        } catch (QueryException) {
            Toaster::error('Internal Server Error');
        }
    }

    public function forceDelete(UserService $service)
    {
        try {
            $this->authorize('forceDelete', $this->modalUser);

            $dto = new ForceDeleteUserDTO($this->modalUser->id);
            $service->forceDelete($dto);

            Toaster::success("User {$this->modalUser->name} dihapus permanen");
            $this->closeModal('forceDelete');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        } catch (QueryException) {
            Toaster::error('Internal Server Error');
        }
    }

    public function render()
    {
        return view('livewire.admin.user-management.trash-bin', [
            'users' => User::onlyTrashed()
                ->select('id', 'nim', 'name', 'deleted_at')
                ->orderByDesc('deleted_at')
                ->paginate($this->paginationIndex),
        ]);
    }
}
