<?php

namespace App\Services\ApiKeys;

use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ApiKeyManager
{
    public function __construct(protected AuditLogger $auditLogger)
    {
    }

    public function listForUser(User $user): Collection
    {
        return $user->tokens()->orderByDesc('created_at')->get();
    }

    public function createToken(
        User $user,
        string $name,
        ?string $description = null,
        array $abilities = ['*'],
        ?CarbonInterface $expiresAt = null,
        ?User $createdBy = null,
        bool $selfService = false
    ): array {
        $abilities = $this->normalizeAbilities($abilities, $selfService);

        $plainTextToken = $user->createToken($name, $abilities, $expiresAt);

        /** @var PersonalAccessToken $token */
        $token = $plainTextToken->accessToken;
        $token->forceFill([
            'description' => $description,
            'last_used_ip' => request()->ip(),
            'last_used_user_agent' => request()->header('User-Agent'),
            'created_by' => $createdBy?->id,
        ])->save();

        $this->auditLogger->log($selfService ? 'api_token_created_self' : 'api_token_created_admin', $token, [], [
            'name' => $name,
            'abilities' => $abilities,
            'expires_at' => $expiresAt?->toIso8601String(),
            'created_by' => $createdBy?->id,
        ]);

        return [
            'token' => $plainTextToken->plainTextToken,
            'id' => $token->id,
        ];
    }

    public function revokeToken(PersonalAccessToken $token, ?User $performedBy = null): void
    {
        $token->delete();

        $this->auditLogger->log(
            $performedBy && $performedBy->id === $token->tokenable_id ? 'api_token_revoked_self' : 'api_token_revoked_admin',
            $token,
            [],
            [
                'performed_by' => $performedBy?->id,
            ]
        );
    }

    public function findTokenForUser(User $user, int $tokenId): ?PersonalAccessToken
    {
        return $user->tokens()->where('id', $tokenId)->first();
    }

    protected function normalizeAbilities(array $abilities, bool $selfService): array
    {
        $abilities = array_values(array_filter(array_unique(array_map(fn ($ability) => Str::lower(trim($ability)), $abilities))));

        if (! $selfService) {
            return $abilities === [] ? ['*'] : $abilities;
        }

        $allowed = config('sanctum.user_abilities', ['read']);
        $filtered = array_values(array_intersect($abilities, $allowed));

        return $filtered === [] ? $allowed : $filtered;
    }
}
