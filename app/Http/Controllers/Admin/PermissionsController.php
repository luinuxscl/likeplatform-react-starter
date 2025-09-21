<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;

class PermissionsController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->string('search');
        $perPage = (int) $request->integer('perPage', 10);

        $permissions = Permission::query()
            ->when($search !== '', fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (Permission $perm) {
                return [
                    'id' => $perm->id,
                    'name' => $perm->name,
                ];
            });

        return Inertia::render('admin/permissions/index', [
            'permissions' => $permissions,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/permissions/create');
    }

    public function store(StorePermissionRequest $request)
    {
        $data = $request->validated();
        Permission::create(['name' => $data['name'], 'guard_name' => 'web']);
        return redirect()->route('admin.permissions.index')->with('success', __('Permiso creado correctamente'));
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('admin/permissions/edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
            ],
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $data = $request->validated();
        $permission->name = $data['name'];
        $permission->save();
        return redirect()->route('admin.permissions.index')->with('success', __('Permiso actualizado correctamente'));
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('admin.permissions.index')->with('success', __('Permiso eliminado correctamente'));
    }
}
