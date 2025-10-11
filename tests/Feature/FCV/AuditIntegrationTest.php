<?php

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\FcvPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Organization;
use Like\Fcv\Models\Person;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(FcvPackageSeeder::class);
    $this->user = User::factory()->create();
});

it('registra cambios en Person en audit_logs', function () {
    $person = Person::factory()->create([
        'rut' => '99999999k',
        'name' => 'Test Person',
    ]);

    // Verificar que se creó un registro de auditoría
    $auditLog = AuditLog::query()
        ->where('auditable_type', Person::class)
        ->where('auditable_id', $person->id)
        ->where('action', 'created')
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->new_values)->toHaveKey('rut');
    expect($auditLog->new_values['rut'])->toBe('99999999k');
});

it('registra actualizaciones en Organization en audit_logs', function () {
    $org = Organization::factory()->create(['name' => 'Original Name']);

    $org->update(['name' => 'Updated Name']);

    $auditLog = AuditLog::query()
        ->where('auditable_type', Organization::class)
        ->where('auditable_id', $org->id)
        ->where('action', 'updated')
        ->latest()
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->old_values['name'])->toBe('Original Name');
    expect($auditLog->new_values['name'])->toBe('Updated Name');
});

it('registra verificaciones de acceso en audit_logs', function () {
    $response = $this->actingAs($this->user)
        ->post('/fcv/verify', ['rut' => '12345678k']);

    $response->assertOk();

    $auditLog = AuditLog::query()
        ->where('action', 'fcv.access.verification')
        ->latest()
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->metadata)->toHaveKey('rut');
    expect($auditLog->metadata)->toHaveKey('allowed');
    expect($auditLog->metadata)->toHaveKey('status');
});

it('registra entradas en audit_logs', function () {
    $response = $this->actingAs($this->user)
        ->post('/fcv/access', [
            'rut' => '12345678k',
            'direction' => 'entrada',
            'status' => 'permitido',
            'reason' => 'Test entry',
        ]);

    $response->assertOk();

    $auditLog = AuditLog::query()
        ->where('action', 'fcv.access.entry')
        ->latest()
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->metadata['direction'])->toBe('entrada');
    expect($auditLog->metadata['status'])->toBe('permitido');
});

it('registra salidas en audit_logs', function () {
    $response = $this->actingAs($this->user)
        ->post('/fcv/access', [
            'rut' => '12345678k',
            'direction' => 'salida',
            'status' => 'permitido',
            'reason' => 'Test exit',
        ]);

    $response->assertOk();

    $auditLog = AuditLog::query()
        ->where('action', 'fcv.access.exit')
        ->latest()
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->metadata['direction'])->toBe('salida');
});

it('registra eliminación de Course en audit_logs', function () {
    $course = Course::factory()->create();
    $courseId = $course->id;

    $course->delete();

    $auditLog = AuditLog::query()
        ->where('auditable_type', Course::class)
        ->where('auditable_id', $courseId)
        ->where('action', 'deleted')
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->old_values)->toHaveKey('name');
});
