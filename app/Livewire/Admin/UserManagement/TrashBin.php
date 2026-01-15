<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
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

    public $modalUser;

    public $modals = [
        'restore' => false,
        'forceDelete' => false,
    ];

    protected string $paginationTheme = 'tailwind';

    protected $rules = [
        'restore' => [
            'id' => 'required|numeric',
        ],
        'forceDelete' => [
            'id' => 'required|numeric',
        ],
    ];

    public function openModal(string $type, int $id)
    {
        try{

            $this->modalUser = User::onlyTrashed()
                                ->findOrFail($id);
                                
            $this->modals[$type] = true;

        }catch(QueryException $e){

            Toaster::error("Gagal memuat data!!");
            
            $this->closeModal($type);
        }
    }

    public function closeModal(string $type)
    {
        $this->modals[$type] = false;
        $this->modalUser = null;
    }

    public function restore()
    {
        try {
            $this->authorize('restore', $this->modalUser);

            $this->validate($this->rules[$this->currentState]);

            DB::transaction(function () {
                $user = User::onlyTrashed()
                    ->whereKey($this->modalUser->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $user->restore();
            });

            Toaster::success("User {$this->modalUser->name} berhasil dipulihkan");

            $this->closeModal('restore');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin untuk restore user');
        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        } catch (QueryException $e) {
            Toaster::error('Internal Server Error: ' . $e->getMessage());
        }
    }

    public function forceDelete()
    {
        try {
            $this->authorize('forceDelete', $this->modalUser);

            $this->validate($this->rules[$this->currentState]);

            DB::transaction(function () {
                $user = User::onlyTrashed()
                    ->whereKey($this->modalUser->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $user->forceDelete();
            });

            Toaster::success("User {$this->modalUser->name} dihapus permanen");
            $this->closeModal('forceDelete');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin untuk menghapus permanen');
        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        } catch (QueryException $e) {
            Toaster::error('Internal Server Error: ' . $e->getMessage());
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
