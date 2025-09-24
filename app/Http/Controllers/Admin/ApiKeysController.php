<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApiKeyRequest;
use App\Models\PersonalAccessToken;
use App\Models\User;
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
        $perPage = (int) $request->integer('perPage', 15);
        $search = (string) $request->string('search');
        $userId = $request->integer('user');
        $issuedBy = (string) $request->string('issuedBy', '');

        $tokens = PersonalAccessToken::query()
            ->with(['user', 'createdBy'])
            ->when($userId, fn ($q) => $q->where('tokenable_id', $userId)->where('tokenable_type', User::class))
            ->when($issuedBy === 'admin', fn ($q) => $q->whereNotNull('created_by')->whereColumn('created_by', '!=', 'tokenable_id'))
            ->when($issuedBy === 'self', fn ($q) => $q->whereColumn('created_by', 'tokenable_id'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (PersonalAccessToken $token) {
                $selfManaged = $token->createdBy && $token->user && $token->createdBy->is($token->user);

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
                    'user' => $token->user ? [
                        'id' => $token->user->id,
                        'name' => $token->user->name,
                        'email' => $token->user->email,
                    ] : null,
                    'created_by' => $token->createdBy ? [
                        'id' => $token->createdBy->id,
                        'name' => $token->createdBy->name,
                        'email' => $token->createdBy->email,
                    ] : null,
                    'self_managed' => $selfManaged,
                ];
            });

        $users = User::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/api-keys/index', [
            'tokens' => $tokens,
            'filters' => [
                'user' => $userId,
                'search' => $search,
                'perPage' => $perPage,
                'issuedBy' => $issuedBy,
            ],
            'users' => $users,
            'generatedToken' => session('generated_api_token'),
            'allowedAbilities' => config('sanctum.user_abilities', []),
        ]);
    }

    public function store(StoreApiKeyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::findOrFail($data['user_id']);
        $abilities = $data['abilities'] ?? ['*'];
        $expiresAt = $data['expires_at'] ? now()->parse($data['expires_at']) : null;

        $result = $this->manager->createToken(
            $user,
            $data['name'],
            $data['description'] ?? null,
            $abilities,
            $expiresAt,
            Auth::user(),
            $user->is(Auth::user()),
        );

        return back()->with('generated_api_token', $result['token'])->with('success', __('API key generada correctamente.'));
    }

    public function destroy(PersonalAccessToken $token): RedirectResponse
    {
        $this->manager->revokeToken($token, Auth::user());

        return back()->with('success', __('API key revocada correctamente.'));
    }
}
