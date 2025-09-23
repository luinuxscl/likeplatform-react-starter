<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->hasRole('admin') || $auth->can('roles.view');
    }

    public function view(User $auth, Role $role): bool
    {
        return $this->viewAny($auth);
    }

    public function create(User $auth): bool
    {
        return $auth->hasRole('admin') || $auth->can('roles.create');
    }

    public function update(User $auth, Role $role): bool
    {
        return $auth->hasRole('admin') || $auth->can('roles.update');
    }

    public function delete(User $auth, Role $role): bool
    {
        if ($role->name === 'admin') {
            return false; // no eliminar rol admin
        }
        return $auth->hasRole('admin') || $auth->can('roles.delete');
    }
}
