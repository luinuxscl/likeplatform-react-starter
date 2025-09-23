<?php

use App\Models\User;
use Database\Seeders\AdminRoleSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(AdminRoleSeeder::class);
});

function makeAdmin(): User {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
    $user->assignRole('admin');
    return $user;
}

it('shows roles index for admin', function () {
    $admin = makeAdmin();
    $this->actingAs($admin)
        ->get('/admin/roles')
        ->assertOk();
});

it('creates a role', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->post('/admin/roles', [
            'name' => 'editor',
            'permissions' => ['users.view'],
        ])
        ->assertRedirect('/admin/roles');

    expect(Role::where('name', 'editor')->exists())->toBeTrue();
});

it('prevents deleting admin role', function () {
    $admin = makeAdmin();
    $role = Role::where('name', 'admin')->firstOrFail();

    $this->actingAs($admin)
        ->delete("/admin/roles/{$role->id}")
        ->assertSessionHas('error');
});
