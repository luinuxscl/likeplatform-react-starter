<?php

use Database\Seeders\FcvPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Migraciones del paquete ya registradas en el provider
    $this->seed(FcvPackageSeeder::class);
});

function makeAuthUser(): User {
    return User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
}

it('permite acceso a funcionario FCV (acceso_total)', function () {
    $user = makeAuthUser();

    $response = $this->actingAs($user)->post('/fcv/verify', [
        'rut' => '12345678k',
    ]);

    $response->assertOk();
    $response->assertJson([
        'allowed' => true,
        'status' => 'permitido',
    ]);
    $response->assertJsonPath('person.rut', '12345678k');
});

it('permite acceso a alumno Cruz de los Andes (horario_flexible)', function () {
    $user = makeAuthUser();

    $response = $this->actingAs($user)->post('/fcv/verify', [
        'rut' => '22222222k',
    ]);

    $response->assertOk();
    $response->assertJson([
        'allowed' => true,
        'status' => 'permitido',
    ]);
});

it('maneja alumno FCV con horario estricto (stub: permitido por ahora)', function () {
    $user = makeAuthUser();

    $response = $this->actingAs($user)->post('/fcv/verify', [
        'rut' => '11111111k',
    ]);

    $response->assertOk();
    $response->assertJson([
        'status' => 'permitido',
    ]);
});

it('deniega persona no registrada', function () {
    $user = makeAuthUser();

    $response = $this->actingAs($user)->post('/fcv/verify', [
        'rut' => '99999999k',
    ]);

    $response->assertOk();
    $response->assertJson([
        'allowed' => false,
        'status' => 'denegado',
        'reason' => 'Persona no registrada',
    ]);
});
