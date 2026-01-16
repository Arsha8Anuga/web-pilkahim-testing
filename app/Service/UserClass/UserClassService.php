<?php

namespace App\Service\UserClass;

use App\Models\UserClass;
use App\DTO\UserClass\CreateUserClassDTO;
use App\DTO\UserClass\UpdateUserClassDTO;
use App\DTO\UserClass\DeleteUserClassDTO;
use Illuminate\Support\Facades\DB;

class UserClassService
{
    public function create(CreateUserClassDTO $dto): UserClass
    {
        return DB::transaction(function () use ($dto) {
            return UserClass::create($dto->toArray());
        });
    }

    public function update(UserClass $class, UpdateUserClassDTO $dto): UserClass
    {
        return DB::transaction(function () use ($class, $dto) {
            $locked = $this->lock($class->id);

            $locked->update($dto->toArray());

            return $locked;
        });
    }

    public function delete(DeleteUserClassDTO $dto): UserClass
    {
        return DB::transaction(function () use ($dto) {
            $class = $this->lock($dto->id);

            // optional guard: tidak boleh hapus class yg masih punya user
            if ($class->users()->exists()) {
                throw new \RuntimeException('Class masih memiliki user');
            }

            $class->delete();

            return $class;
        });
    }

    private function lock(int $id): UserClass
    {
        return UserClass::query()
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
