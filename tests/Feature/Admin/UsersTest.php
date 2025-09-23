<?php

use App\Models\User;
use Database\Seeders\AdminRoleSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(AdminRoleSeeder::class);
});

function makeAdminUser(): User {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
    $user->assignRole('admin');
    return $user;
}

function makeRegularUser(): User {
    return User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
}

it('allows admin to access users index', function () {
    $admin = makeAdminUser();
    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk();
});

it('forbids non-admin to access users index', function () {
    $user = makeRegularUser();
    $this->actingAs($user)
        ->get('/admin/users')
        ->assertForbidden();
});

it('allows admin to create a user', function () {
    $admin = makeAdminUser();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'verified' => true,
            'roles' => ['admin'],
        ])
        ->assertRedirect('/admin/users');

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('prevents deleting the only admin user', function () {
    $admin = makeAdminUser();

    $this->actingAs($admin)
        ->delete("/admin/users/{$admin->id}")
        ->assertSessionHas('error');
});
