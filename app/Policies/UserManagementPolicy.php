<?php

namespace App\Policies;

use App\Models\User;

class UserManagementPolicy
{
    
    public function delete(User $authUser, User $targetUser){
        return $authUser->isAdmin() && $authUser !== $targetUser;
    }

    public function update(User $authUser){
        return $authUser->isAdmin();
    }

    public function restore(User $authUser, User $targetUser): bool {
        return $authUser->id !== $targetUser->id;
    }

    public function forceDelete(User $authUser, User $targetUser): bool{
        return $authUser->id !== $targetUser->id;
    }


}
