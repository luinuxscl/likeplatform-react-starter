<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->hasRole('admin') || $auth->can('permissions.view');
    }

    public function view(User $auth, Permission $permission): bool
    {
        return $this->viewAny($auth);
    }

    public function create(User $auth): bool
    {
        return $auth->hasRole('admin') || $auth->can('permissions.create');
    }

    public function update(User $auth, Permission $permission): bool
    {
        return $auth->hasRole('admin') || $auth->can('permissions.update');
    }

    public function delete(User $auth, Permission $permission): bool
    {
        return $auth->hasRole('admin') || $auth->can('permissions.delete');
    }
}
