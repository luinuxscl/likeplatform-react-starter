<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('registra auditoría para el ciclo de vida de usuarios', function () {
    $admin = User::factory()->create();
    Role::findOrCreate('admin');
    $admin->assignRole('admin');
    actingAs($admin);

    $user = User::factory()->create([
        'name' => 'Usuario de prueba',
    ]);

    expect(AuditLog::where('auditable_id', $user->id)->where('action', 'created')->exists())->toBeTrue();

    $user->update(['name' => 'Usuario actualizado']);

    $updatedLog = AuditLog::where('auditable_id', $user->id)
        ->where('action', 'updated')
        ->latest('id')
        ->first();

    expect($updatedLog)->not->toBeNull();
    expect($updatedLog->new_values['name'] ?? null)->toBe('Usuario actualizado');

    $userId = $user->id;
    $user->delete();

    expect(AuditLog::where('auditable_id', $userId)->where('action', 'deleted')->exists())->toBeTrue();
});
