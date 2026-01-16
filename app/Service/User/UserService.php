<?php

namespace App\Service\User;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\DeleteUserDTO;
use App\DTO\User\ForceDeleteUserDTO;
use App\DTO\User\RestoreUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function create(CreateUserDTO $dto): User {
        return DB::transaction(function () use ($dto) {
            return User::create($dto->toArray());
        });
    }

    public function update(User $user, UpdateUserDTO $dto): User{
        return DB::transaction(function () use ($user, $dto) {
            $lockedUser = $this->lockUser($user->id);

            $lockedUser->update($dto->toArray());

            return $lockedUser;
        });
    }

    public function delete(DeleteUserDTO $dto): User {
        return DB::transaction(function () use ($dto) {
            $user = $this->lockUser($dto->id);

            $user->delete();

            return $user;
        });
    }

    public function forceDelete(ForceDeleteUserDTO $dto): User {
        return DB::transaction(function () use ($dto) {
            $user = $this->lockUser($dto->id, true);

            $user->forceDelete();

            return $user;
        });
    }

    public function restore(RestoreUserDTO $dto): User {
        return DB::transaction(function () use ($dto) {
            $user = $this->lockUser($dto->id, true);

            $user->restore();

            return $user;
        });
    }

    private function lockUser(int $id, bool $onlyTrashed = false): User {
        $query = $onlyTrashed
            ? User::onlyTrashed()
            : User::query();

        return $query
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
