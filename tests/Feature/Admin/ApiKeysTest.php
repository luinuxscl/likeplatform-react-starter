<?php

use App\Models\AuditLog;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Services\ApiKeys\ApiKeyManager;
use Database\Seeders\AdminRoleSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(AdminRoleSeeder::class);
});

function makeAdminForApiKeys(): User
{
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);

    $admin->assignRole('admin');

    return $admin;
}

function makeUserForApiKeys(): User
{
    return User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
}

it('permite al administrador crear API keys para cualquier usuario', function () {
    $admin = makeAdminForApiKeys();
    $targetUser = makeUserForApiKeys();

    $response = $this->from('/admin/api-keys')->actingAs($admin)->post('/admin/api-keys', [
        'user_id' => $targetUser->id,
        'name' => 'Integración CRM',
        'description' => 'Clave para la nueva integración',
        'abilities' => ['*'],
        'expires_at' => null,
    ]);

    $response->assertRedirect('/admin/api-keys');
    $response->assertSessionHas('generated_api_token');
    $response->assertSessionHas('success');

    $token = PersonalAccessToken::query()
        ->where('tokenable_id', $targetUser->id)
        ->orderByDesc('id')
        ->first();

    expect($token)->not()->toBeNull();
    expect($token->created_by)->toBe($admin->id);
    expect($token->name)->toBe('Integración CRM');
    expect($token->abilities)->toEqual(['*']);

    $audit = AuditLog::query()->latest('id')->first();

    expect($audit)->not()->toBeNull();
    expect($audit->action)->toBe('api_token_created_admin');
    expect($audit->auditable_id)->toBe($token->id);
    expect($audit->user_id)->toBe($admin->id);
});

it('permite al administrador revocar API keys existentes', function () {
    $admin = makeAdminForApiKeys();
    $targetUser = makeUserForApiKeys();

    /** @var ApiKeyManager $manager */
    $manager = app(ApiKeyManager::class);

    $result = $manager->createToken(
        $targetUser,
        'Integración Legacy',
        'Token generado para pruebas',
        ['*'],
        null,
        $admin,
        false,
    );

    $tokenId = $result['id'];

    $response = $this->from('/admin/api-keys')->actingAs($admin)->delete("/admin/api-keys/{$tokenId}");

    $response->assertRedirect('/admin/api-keys');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);

    $audit = AuditLog::query()->latest('id')->first();

    expect($audit)->not()->toBeNull();
    expect($audit->action)->toBe('api_token_revoked_admin');
    expect($audit->auditable_id)->toBe($tokenId);
    expect($audit->user_id)->toBe($admin->id);
});
