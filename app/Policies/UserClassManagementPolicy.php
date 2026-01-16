<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserClass;

class UserClassPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->isAdmin();
    }

    public function view(User $authUser, UserClass $class): bool
    {
        return $authUser->isAdmin();
    }

    public function create(User $authUser): bool
    {
        return $authUser->isAdmin();
    }

    public function update(User $authUser, UserClass $class): bool
    {
        return $authUser->isAdmin();
    }

    public function delete(User $authUser, UserClass $class): bool
    {
        return $authUser->isAdmin();
    }
}
