<?php

namespace App\Livewire\Admin\UserManagement;

use App\Concerns\WithAudit;
use App\DTO\User\CreateUserDTO;
use App\DTO\User\DeleteUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Enums\AuditLogAction;
use App\Models\User;
use App\Models\UserClass;
use App\Service\User\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class Index extends Component
{
    use WithPagination, AuthorizesRequests, WithAudit;

    #[Title("User Management")]

    public int $paginationIndex = 10;
    public string $currentState = '';

    public array $modals = [
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

    public Collection $userClasses;

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public ?int $filterClass = null;
    public ?string $filterStatus = null;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function openModal(string $type, $id = null){
        try{ 
            if (in_array($type, ['detail', 'update', 'delete'])) {
                $this->modalUser = User::with('classes')->findOrFail($id);
            }
    
            if (in_array($type, ['update','create'])){
                $this->userClasses = UserClass::all();
            }
    
            $this->currentState = $type;
            $this->modals[$type] = true;
        }catch (ModelNotFoundException){
            Toaster::error('User tidak ditemukan');
        }
    }

    public function closeModal(string $type){
        $this->modals[$type] = false;
        $this->currentState = '';
        $this->modalUser = null;
        $this->userClasses = collect();

    }

    public function create(UserService $service){
        try {

            $user = $this->withAudit(
                AuditLogAction::USER_CREATE,
                function () use ($service) {

                    $this->authorize('create');

                    $data = $this->validate(CreateUserDTO::rules());
                    $dto  = CreateUserDTO::from($data);

                    return $service->create($dto);
                }
            );

            Toaster::success("User berhasil dibuat: {$user->name}");
            $this->closeModal('create');

        } catch (ValidationException) {
            Toaster::error('Validasi gagal');
        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }

    public function update(UserService $service){
        try {

            $this->withAudit(
                AuditLogAction::USER_UPDATE,
                function () use ($service) {

                    if (! $this->modalUser) {
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('update', $this->modalUser);

                    $data = $this->validate(UpdateUserDTO::rules());
                    $dto  = UpdateUserDTO::from($data);

                    $service->update($this->modalUser, $dto);
                },
                [
                    'target_user_id' => $this->modalUser?->id,
                ]
            );

            Toaster::success("User {$this->modalUser->name} berhasil diperbarui");
            $this->closeModal('update');

        } catch (ValidationException) {
            Toaster::error('Validasi gagal');
        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('User tidak ditemukan');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }

   public function delete(UserService $service){
        try {

            $this->withAudit(
                AuditLogAction::USER_DELETE,
                function () use ($service){

                    if(! $this->modalUser){
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('delete', $this->modalUser);

                    $dto = new DeleteUserDTO($this->modalUser->id);
                    $service->delete($dto);
                },[
                    'deleted_user_id' => $this->modalUser->id
                ]
            );

            Toaster::success("User {$this->modalUser->name} berhasil dihapus");
            $this->closeModal('delete');

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

    public function updatedFilterClass(){
        $this->resetPage();
    }

    public function updatedFilterStatus(){
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

    public function mount(){
        $this->userClasses = collect();
    }

    public function render(){
        $users = User::query()
            ->select('id', 'nim', 'name', 'id_class', 'status', 'created_at')
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
