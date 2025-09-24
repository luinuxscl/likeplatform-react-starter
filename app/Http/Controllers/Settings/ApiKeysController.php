<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreUserApiKeyRequest;
use App\Models\PersonalAccessToken;
use App\Services\ApiKeys\ApiKeyManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApiKeysController extends Controller
{
    public function __construct(protected ApiKeyManager $manager)
    {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        $tokens = PersonalAccessToken::query()
            ->ownedBy($user)
            ->orderByDesc('created_at')
            ->paginate((int) $request->integer('perPage', 10))
            ->withQueryString()
            ->through(function (PersonalAccessToken $token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'description' => $token->description,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at,
                    'last_used_ip' => $token->last_used_ip,
                    'last_used_user_agent' => $token->last_used_user_agent,
                    'expires_at' => $token->expires_at,
                    'created_at' => $token->created_at,
                ];
            });

        return Inertia::render('settings/api-keys/index', [
            'tokens' => $tokens,
            'allowedAbilities' => config('sanctum.user_abilities', []),
            'generatedToken' => session('generated_api_token'),
        ]);
    }

    public function store(StoreUserApiKeyRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $abilities = $data['abilities'] ?? [];
        $expiresAt = $data['expires_at'] ? now()->parse($data['expires_at']) : null;

        $result = $this->manager->createToken(
            $user,
            $data['name'],
            $data['description'] ?? null,
            $abilities,
            $expiresAt,
            $user,
            true,
        );

        return back()->with('generated_api_token', $result['token'])->with('success', __('API key generada correctamente.'));
    }

    public function destroy(Request $request, PersonalAccessToken $token): RedirectResponse
    {
        $user = $request->user();

        abort_unless($token->tokenable_id === $user->getKey() && $token->tokenable_type === $user->getMorphClass(), 403);

        $this->manager->revokeToken($token, $user);

        return back()->with('success', __('API key revocada correctamente.'));
    }
}
