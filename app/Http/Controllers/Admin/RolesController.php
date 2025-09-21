<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;

class RolesController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->string('search');
        $perPage = (int) $request->integer('perPage', 10);

        $roles = Role::query()
            ->when($search !== '', fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions()->pluck('name'),
                ];
            });

        return Inertia::render('admin/roles/index', [
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        $permissions = Permission::orderBy('name')->pluck('name');
        return Inertia::render('admin/roles/create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);
        return redirect()->route('admin.roles.index')->with('success', __('Rol creado correctamente'));
    }

    public function edit(Role $role): Response
    {
        $permissions = Permission::orderBy('name')->pluck('name');
        return Inertia::render('admin/roles/edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name'),
            ],
            'permissions' => $permissions,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $request->validated();
        $role->name = $data['name'];
        $role->save();
        $role->syncPermissions($data['permissions'] ?? []);
        return redirect()->route('admin.roles.index')->with('success', __('Rol actualizado correctamente'));
    }

    public function destroy(Role $role)
    {
        // Evitar eliminar el rol admin por seguridad básica
        if ($role->name === 'admin') {
            return back()->with('error', __('No puedes eliminar el rol administrador.'));
        }
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', __('Rol eliminado correctamente'));
    }
}
