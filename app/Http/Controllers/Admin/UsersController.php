<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;

class UsersController extends Controller
{
    private function isLastAdmin(User $user): bool
    {
        if (! $user->hasRole('admin')) {
            return false;
        }

        // Count how many users have the admin role
        $adminCount = User::role('admin')->count();
        return $adminCount === 1; // this user is the only admin
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);
        $search = (string) $request->string('search');
        $perPage = (int) $request->integer('perPage', 10);

        $users = User::query()
            ->with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'roles' => $user->roles->pluck('name'),
                    'created_at' => $user->created_at,
                ];
            });

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);
        $roles = Role::query()->orderBy('name')->pluck('name');

        return Inertia::render('admin/users/create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => $request->boolean('verified') ? now() : null,
        ]);

        if (! empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return redirect()->route('admin.users.index')->with('success', __('Usuario creado correctamente'));
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);
        $roles = Role::query()->orderBy('name')->pluck('name');

        return Inertia::render('admin/users/edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'roles' => $user->roles->pluck('name'),
            ],
            'roles' => $roles,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $data = $request->validated();

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->email_verified_at = $request->boolean('verified') ? ($user->email_verified_at ?? now()) : null;
        $user->save();

        // Prevent removing admin role from the last admin
        if ($this->isLastAdmin($user)) {
            $roles = $data['roles'] ?? [];
            if (! in_array('admin', $roles, true)) {
                return back()->with('error', __('No puedes quitar el rol administrador del único usuario administrador.'));
            }
        }

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('success', __('Usuario actualizado correctamente'));
    }

    public function destroy(Request $request, User $user)
    {
        // Evitar que un usuario se elimine a sí mismo
        if ($request->user()->id === $user->id) {
            return back()->with('error', __('No puedes eliminar tu propio usuario.'));
        }

        // Evitar eliminar al único usuario con rol admin
        if ($this->isLastAdmin($user)) {
            return back()->with('error', __('No puedes eliminar al único usuario con rol administrador.'));
        }

        // Autorización formal (policies)
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', __('Usuario eliminado correctamente'));
    }
}
