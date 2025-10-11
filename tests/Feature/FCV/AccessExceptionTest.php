<?php

use App\Models\User;
use Database\Seeders\FcvPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Like\Fcv\Models\AccessException;
use Like\Fcv\Models\Person;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(FcvPackageSeeder::class);
    $this->user = User::factory()->create();
    $this->person = Person::factory()->create();
});

it('crea una excepción de acceso', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/fcv/access-exceptions', [
            'person_id' => $this->person->id,
            'reason' => 'medical',
            'description' => 'Cita médica urgente',
            'valid_from' => now()->toDateTimeString(),
            'valid_until' => now()->addDays(2)->toDateTimeString(),
        ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'message',
        'exception' => ['id', 'person_id', 'reason', 'status'],
    ]);

    $exception = AccessException::latest()->first();
    expect($exception->person_id)->toBe($this->person->id);
    expect($exception->reason)->toBe('medical');
    expect($exception->status)->toBe('pending');
    expect($exception->created_by)->toBe($this->user->id);
});

it('lista excepciones con filtros', function () {
    AccessException::factory()->count(5)->create([
        'person_id' => $this->person->id,
        'status' => 'pending',
    ]);

    AccessException::factory()->count(3)->create([
        'status' => 'approved',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/fcv/access-exceptions?status=pending');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(5);
});

it('aprueba una excepción pendiente', function () {
    $exception = AccessException::factory()->create([
        'person_id' => $this->person->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/fcv/access-exceptions/{$exception->id}/approve");

    $response->assertOk();
    $response->assertJson(['message' => 'Excepción aprobada exitosamente']);

    $exception->refresh();
    expect($exception->status)->toBe('approved');
    expect($exception->approved_by)->toBe($this->user->id);
});

it('rechaza una excepción pendiente', function () {
    $exception = AccessException::factory()->create([
        'person_id' => $this->person->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/fcv/access-exceptions/{$exception->id}/reject", [
            'rejection_reason' => 'No cumple requisitos',
        ]);

    $response->assertOk();
    $response->assertJson(['message' => 'Excepción rechazada']);

    $exception->refresh();
    expect($exception->status)->toBe('rejected');
    expect($exception->rejection_reason)->toBe('No cumple requisitos');
});

it('no permite aprobar excepción ya aprobada', function () {
    $exception = AccessException::factory()->create([
        'status' => 'approved',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/fcv/access-exceptions/{$exception->id}/approve");

    $response->assertStatus(422);
    $response->assertJson(['message' => 'Solo se pueden aprobar excepciones pendientes']);
});

it('no permite editar excepción aprobada', function () {
    $exception = AccessException::factory()->create([
        'status' => 'approved',
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/fcv/access-exceptions/{$exception->id}", [
            'description' => 'Nueva descripción',
        ]);

    $response->assertStatus(422);
    $response->assertJson(['message' => 'Solo se pueden editar excepciones pendientes']);
});

it('verifica excepción activa en verificación de acceso', function () {
    $person = Person::query()->where('rut', '11111111k')->first();

    // Crear excepción activa
    AccessException::factory()->create([
        'person_id' => $person->id,
        'status' => 'approved',
        'reason' => 'medical',
        'valid_from' => now()->subHour(),
        'valid_until' => now()->addHour(),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/fcv/verify', ['rut' => '11111111k']);

    $response->assertOk();
    $response->assertJson([
        'allowed' => true,
        'status' => 'permitido',
    ]);
    $response->assertJsonPath('reason', fn ($reason) => str_contains($reason, 'Excepción activa'));
    expect($response->json('exception'))->not->toBeNull();
});

it('ignora excepción vencida', function () {
    $person = Person::query()->where('rut', '11111111k')->first();

    // Crear excepción vencida
    AccessException::factory()->create([
        'person_id' => $person->id,
        'status' => 'approved',
        'valid_from' => now()->subDays(3),
        'valid_until' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/fcv/verify', ['rut' => '11111111k']);

    $response->assertOk();
    // No debe mencionar excepción
    expect($response->json('exception'))->toBeNull();
});

it('ignora excepción pendiente de aprobación', function () {
    $person = Person::query()->where('rut', '11111111k')->first();

    // Crear excepción pendiente
    AccessException::factory()->create([
        'person_id' => $person->id,
        'status' => 'pending',
        'valid_from' => now()->subHour(),
        'valid_until' => now()->addHour(),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/fcv/verify', ['rut' => '11111111k']);

    $response->assertOk();
    // No debe mencionar excepción
    expect($response->json('exception'))->toBeNull();
});

it('obtiene excepciones activas de una persona', function () {
    AccessException::factory()->create([
        'person_id' => $this->person->id,
        'status' => 'approved',
        'valid_from' => now()->subHour(),
        'valid_until' => now()->addHour(),
    ]);

    AccessException::factory()->create([
        'person_id' => $this->person->id,
        'status' => 'approved',
        'valid_from' => now()->subDays(2),
        'valid_until' => now()->subDay(), // Vencida
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/fcv/access-exceptions/person/{$this->person->id}/active");

    $response->assertOk();
    expect($response->json('active_exceptions'))->toHaveCount(1);
});

it('scope active filtra correctamente', function () {
    AccessException::factory()->create([
        'status' => 'approved',
        'valid_from' => now()->subHour(),
        'valid_until' => now()->addHour(),
    ]);

    AccessException::factory()->create([
        'status' => 'pending',
        'valid_from' => now()->subHour(),
        'valid_until' => now()->addHour(),
    ]);

    AccessException::factory()->create([
        'status' => 'approved',
        'valid_from' => now()->subDays(2),
        'valid_until' => now()->subDay(),
    ]);

    $activeCount = AccessException::active()->count();
    expect($activeCount)->toBe(1);
});
