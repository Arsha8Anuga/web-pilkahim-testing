<?php

namespace App\Policies;

use App\Models\User;

class UserManagementPolicy
{
    
    public function delete(User $authUser, User $targetUser): bool {
        return $authUser->isAdmin() && $authUser !== $targetUser;
    }

    public function update(User $authUser): bool {
        return $authUser->isAdmin();
    }

    public function restore(User $authUser, User $targetUser): bool {
        return $authUser->isAdmin() && $authUser->id !== $targetUser->id;
    }

    public function forceDelete(User $authUser, User $targetUser): bool{
        return $authUser->isAdmin() && $authUser->id !== $targetUser->id;
    }

    public function create(User $authUser): bool {
        return $authUser->isAdmin();
    }


}
