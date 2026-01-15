<?php

namespace App\Livewire\Admin\UserManagement;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\DeleteUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Models\User;
use App\Models\UserClass;
use App\Service\User\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Title("User Management")]

    public int $paginationIndex = 10;
    public string $currentState = '';

    public $modals = [
        'detail' => false,
        'create' => false,
        'update' => false,
        'delete' => false
    ];

    protected array $sortable = [
        'name',
        'nim',
        'status',
        'created_at',
    ];

    public ?User $modalUser;

    public $userClasses = [];

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public ?int $filterClass = null;
    public ?string $filterStatus = null;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';


    public function openModal(string $type, $id = null)
    {
        if (in_array($type, ['detail', 'update', 'delete'])) {
            $this->modalUser = User::with('classes')->findOrFail($id);
        }

        if (in_array($type, ['update','create'])){
            $this->userClasses = UserClass::all();
        }

        $this->currentState = $type;
        $this->modals[$type] = true;
    }

    public function closeModal(string $type)
    {
        $this->modals[$type] = false;
        $this->currentState = '';
        $this->modalUser = null;
    }

    public function create(UserService $service)
    {
        try {
            $this->authorize('create');

            $data = $this->validate(CreateUserDTO::rules());
            $dto  = CreateUserDTO::from($data);

            $user = $service->create($dto);

            Toaster::success("User berhasil dibuat: {$user->name}");
            $this->closeModal('create');

        } catch (ValidationException $e) {
            Toaster::error($e->validator->errors()->first());
        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (QueryException) {
            Toaster::error('Internal Server Error');
        }
    }

    public function update(UserService $service)
    {
        try {
            $this->authorize('update', $this->modalUser);

            $data = $this->validate(UpdateUserDTO::rules());
            $dto  = UpdateUserDTO::from($data);

            $service->update($this->modalUser, $dto);

            Toaster::success("User {$this->modalUser->name} berhasil diperbarui");
            $this->closeModal('update');

        } catch (ValidationException $e) {
            Toaster::error($e->validator->errors()->first());
        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        } catch (QueryException) {
            Toaster::error('Internal Server Error');
        }
    }

    public function delete(UserService $service)
    {
        try {
            $this->authorize('delete', $this->modalUser);

            $dto = new DeleteUserDTO($this->modalUser->id);
            $service->delete($dto);

            Toaster::success("User {$this->modalUser->name} berhasil dihapus");
            $this->closeModal('delete');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        } catch (QueryException) {
            Toaster::error('Internal Server Error');
        }
    }

    public function updatePaginationIndex()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterClass()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function sort(string $field)
    {
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

    public function render()
    {
        $users = User::query()
            ->select('id', 'nim', 'name', 'id_class', 'status')
            ->with('classes:id,name')

            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->search}%")
                    ->orWhere('nim', 'like', "%{$this->search}%");
                });
            })

            ->when($this->filterClass, fn ($q) =>
                $q->where('id_class', $this->filterClass)
            )

            ->when($this->filterStatus, fn ($q) =>
                $q->where('status', $this->filterStatus)
            )

            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->paginationIndex);

        return view('livewire.admin.user-management.index', [
            'users' => $users,
        ]);
    }

}
