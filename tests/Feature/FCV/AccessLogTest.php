<?php

use App\Models\User;
use Database\Seeders\FcvPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Like\Fcv\Models\AccessLog;
use Like\Fcv\Models\Person;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(FcvPackageSeeder::class);
});

function makeAuthUserFcv(): User {
    return User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
}

it('crea un log de acceso con RUT existente', function () {
    $user = makeAuthUserFcv();

    // Asegurar que la persona existe desde el seeder
    $person = Person::query()->where('rut', '12345678k')->firstOrFail();

    $payload = [
        'rut' => '12345678k',
        'direction' => 'entrada',
        'status' => 'permitido',
        'reason' => 'Control manual',
        'meta' => ['device' => 'guard-station-1'],
    ];

    $response = $this->actingAs($user)->post('/fcv/access', $payload);

    $response->assertOk()->assertJson(['ok' => true]);

    $log = AccessLog::query()->latest('id')->first();

    expect($log)->not()->toBeNull();
    expect($log->person_id)->toBe($person->id);
    expect($log->direction)->toBe('entrada');
    expect($log->status)->toBe('permitido');
    expect($log->gatekeeper_id)->toBe($user->id);
    expect($log->meta)->toHaveKey('ip');
    expect($log->meta)->toHaveKey('user_agent');
    expect($log->meta['device'])->toBe('guard-station-1');
});

it('crea un log de acceso sin RUT (p.ej. vehículo o visitante)', function () {
    $user = makeAuthUserFcv();

    $payload = [
        'rut' => null,
        'direction' => 'salida',
        'status' => 'denegado',
        'reason' => 'Salida no autorizada',
    ];

    $response = $this->actingAs($user)->post('/fcv/access', $payload);

    $response->assertOk()->assertJson(['ok' => true]);

    $log = AccessLog::query()->latest('id')->first();

    expect($log)->not()->toBeNull();
    expect($log->person_id)->toBeNull();
    expect($log->direction)->toBe('salida');
    expect($log->status)->toBe('denegado');
    expect($log->gatekeeper_id)->toBe($user->id);
});
