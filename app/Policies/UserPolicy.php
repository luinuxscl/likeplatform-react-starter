<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->hasRole('admin') || $auth->can('users.view');
    }

    public function view(User $auth, User $user): bool
    {
        return $this->viewAny($auth);
    }

    public function create(User $auth): bool
    {
        return $auth->hasRole('admin') || $auth->can('users.create');
    }

    public function update(User $auth, User $user): bool
    {
        return $auth->hasRole('admin') || $auth->can('users.update');
    }

    public function delete(User $auth, User $user): bool
    {
        if ($auth->id === $user->id) {
            return false; // no self-delete
        }
        return $auth->hasRole('admin') || $auth->can('users.delete');
    }
}
