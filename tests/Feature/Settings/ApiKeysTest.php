<?php

use App\Models\AuditLog;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    Config::set('sanctum.user_abilities', ['read', 'write']);
});

function makeSettingsApiKeyUser(): User
{
    return User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
}

it('permite al usuario crear una API key autogestionada dentro de la lista blanca', function () {
    $user = makeSettingsApiKeyUser();

    $response = $this->from('/settings/api-keys')->actingAs($user)->post('/settings/api-keys', [
        'name' => 'Integración personal',
        'description' => 'Uso propio',
        'abilities' => ['read', 'write'],
        'expires_at' => now()->addDays(30)->format('Y-m-d\TH:i'),
    ]);

    $response->assertRedirect('/settings/api-keys');
    $response->assertSessionHas('generated_api_token');
    $response->assertSessionHas('success');

    $token = PersonalAccessToken::query()
        ->where('tokenable_id', $user->id)
        ->latest('id')
        ->first();

    expect($token)->not()->toBeNull();
    expect($token->created_by)->toBe($user->id);
    expect($token->abilities)->toEqual(['read', 'write']);

    $audit = AuditLog::query()->latest('id')->first();

    expect($audit)->not()->toBeNull();
    expect($audit->action)->toBe('api_token_created_self');
    expect($audit->auditable_id)->toBe($token->id);
    expect($audit->user_id)->toBe($user->id);
});

it('asigna la lista blanca completa cuando no se envían habilidades', function () {
    $user = makeSettingsApiKeyUser();

    $response = $this->from('/settings/api-keys')->actingAs($user)->post('/settings/api-keys', [
        'name' => 'Clave por defecto',
        'abilities' => [],
        'description' => null,
        'expires_at' => null,
    ]);

    $response->assertRedirect('/settings/api-keys');

    $token = PersonalAccessToken::query()
        ->where('tokenable_id', $user->id)
        ->latest('id')
        ->first();

    expect($token)->not()->toBeNull();
    expect($token->abilities)->toEqualCanonicalizing(['read', 'write']);
});

it('permite revocar una API key autogestionada', function () {
    $user = makeSettingsApiKeyUser();

    $this->from('/settings/api-keys')->actingAs($user)->post('/settings/api-keys', [
        'name' => 'Token temporal',
        'abilities' => ['read'],
        'description' => null,
        'expires_at' => null,
    ])->assertRedirect('/settings/api-keys');

    $token = PersonalAccessToken::query()
        ->where('tokenable_id', $user->id)
        ->latest('id')
        ->firstOrFail();

    $response = $this->from('/settings/api-keys')->actingAs($user)->delete("/settings/api-keys/{$token->id}");

    $response->assertRedirect('/settings/api-keys');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);

    $audit = AuditLog::query()->latest('id')->first();

    expect($audit)->not()->toBeNull();
    expect($audit->action)->toBe('api_token_revoked_self');
    expect($audit->auditable_id)->toBe($token->id);
    expect($audit->user_id)->toBe($user->id);
});
