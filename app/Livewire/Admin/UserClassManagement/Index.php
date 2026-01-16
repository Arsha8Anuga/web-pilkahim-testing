<?php

namespace App\Livewire\Admin\UserClass;

use App\Concerns\WithAudit;
use App\DTO\UserClass\CreateUserClassDTO;
use App\DTO\UserClass\UpdateUserClassDTO;
use App\DTO\UserClass\DeleteUserClassDTO;
use App\Enums\AuditLogAction;
use App\Models\UserClass;
use App\Service\UserClass\UserClassService;
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
    use WithPagination, AuthorizesRequests, WithAudit;

    #[Title('User Class Management')]

    protected string $paginationTheme = 'tailwind';

    public int $paginationIndex = 10;
    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    protected array $sortable = [
        'name',
        'created_at',
    ];

    public array $modals = [
        'create' => false,
        'update' => false,
        'delete' => false,
    ];

    public ?UserClass $modalClass = null;

    public function openModal(string $type, ?int $id = null): void
    {
        try {
            if (in_array($type, ['update', 'delete'])) {
                $this->modalClass = UserClass::findOrFail($id);
            }

            $this->modals[$type] = true;

        } catch (ModelNotFoundException) {
            Toaster::error('Class tidak ditemukan');
        }
    }

    public function closeModal(string $type): void
    {
        $this->modals[$type] = false;
        $this->modalClass = null;
    }

    public function create(UserClassService $service): void
    {
        try {
            $class = $this->withAudit(
                AuditLogAction::USER_CLASS_CREATE,
                function () use ($service) {

                    $this->authorize('create', UserClass::class);

                    $data = $this->validate(CreateUserClassDTO::rules());
                    $dto  = CreateUserClassDTO::from($data);

                    return $service->create($dto);
                }
            );

            Toaster::success("Class {$class->name} berhasil dibuat");
            $this->closeModal('create');

        } catch (ValidationException) {
            Toaster::error('Validasi gagal');
        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }

    public function update(UserClassService $service): void
    {
        try {
            $this->withAudit(
                AuditLogAction::USER_CLASS_UPDATE,
                function () use ($service) {

                    if (! $this->modalClass) {
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('update', $this->modalClass);

                    $data = $this->validate(
                        UpdateUserClassDTO::rules($this->modalClass->id)
                    );
                    
                    $dto  = UpdateUserClassDTO::from($data);

                    $service->update($this->modalClass, $dto);
                },
                [
                    'class_id' => $this->modalClass?->id,
                ]
            );

            Toaster::success("Class {$this->modalClass->name} berhasil diperbarui");
            $this->closeModal('update');

        } catch (ValidationException) {
            Toaster::error('Validasi gagal');
        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('Class tidak ditemukan');
        } catch (\Throwable) {
            Toaster::error('Internal Server Error');
        }
    }

    public function delete(UserClassService $service): void
    {
        try {
            $this->withAudit(
                AuditLogAction::USER_CLASS_DELETE,
                function () use ($service) {

                    if (! $this->modalClass) {
                        throw new ModelNotFoundException();
                    }

                    $this->authorize('delete', $this->modalClass);

                    $dto = new DeleteUserClassDTO($this->modalClass->id);
                    $service->delete($dto);
                },
                [
                    'class_id' => $this->modalClass?->id,
                ]
            );

            Toaster::success("Class {$this->modalClass->name} berhasil dihapus");
            $this->closeModal('delete');

        } catch (AuthorizationException) {
            Toaster::error('Tidak memiliki izin');
        } catch (ModelNotFoundException) {
            Toaster::error('Class tidak ditemukan');
        } catch (\Throwable $e) {
            Toaster::error($e->getMessage() ?: 'Internal Server Error');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatePaginationIndex(): void
    {
        $this->resetPage();
    }

    public function sort(string $field): void
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
        $classes = UserClass::query()
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->paginationIndex);

        return view('livewire.admin.user-class-management.index', [
            'classes' => $classes,
        ]);
    }
}
