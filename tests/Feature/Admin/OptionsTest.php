<?php

use App\Models\User;
use Database\Seeders\AdminRoleSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(AdminRoleSeeder::class);
});

function makeAdminWithOptionsPerms(): User {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
    $user->assignRole('admin'); // admin ya tiene todos los permisos por seeder
    return $user;
}

function makeRegularNoAdmin(): User {
    return User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
}

it('allows admin with permission to view options index', function () {
    $admin = makeAdminWithOptionsPerms();

    $this->actingAs($admin)
        ->get('/admin/options')
        ->assertOk();
});

it('forbids non-admin to view options index', function () {
    $user = makeRegularNoAdmin();

    $this->actingAs($user)
        ->get('/admin/options')
        ->assertForbidden();
});

it('allows admin with permission to update options', function () {
    $admin = makeAdminWithOptionsPerms();

    $this->actingAs($admin)
        ->put('/admin/options', [
            'values' => [
                'app.name' => 'NuevaApp',
            ],
        ])
        ->assertRedirect();
});

it('forbids non-admin to update options', function () {
    $user = makeRegularNoAdmin();

    $this->actingAs($user)
        ->put('/admin/options', [
            'values' => [
                'app.name' => 'NoDebePermitir',
            ],
        ])
        ->assertForbidden();
});
