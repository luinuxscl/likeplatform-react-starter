<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditSessionsController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'user' => $request->integer('user'),
            'active' => $request->boolean('active', false),
            'search' => (string) $request->string('search'),
        ];
        $perPage = (int) $request->integer('perPage', 15);

        $sessions = UserSession::query()
            ->with('user')
            ->when($filters['user'], fn ($q) => $q->where('user_id', $filters['user']))
            ->when($filters['active'], fn ($q) => $q->whereNull('logout_at'))
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $term = $filters['search'];
                $q->where(function ($inner) use ($term) {
                    $inner->where('ip_address', 'like', "%{$term}%")
                        ->orWhere('user_agent', 'like', "%{$term}%")
                        ->orWhere('device', 'like', "%{$term}%")
                        ->orWhere('platform', 'like', "%{$term}%")
                        ->orWhere('browser', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('last_activity_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (UserSession $session) {
                return [
                    'id' => $session->id,
                    'session_id' => $session->session_id,
                    'user' => $session->user ? [
                        'id' => $session->user->id,
                        'name' => $session->user->name,
                        'email' => $session->user->email,
                    ] : null,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'device' => $session->device,
                    'platform' => $session->platform,
                    'browser' => $session->browser,
                    'login_at' => $session->login_at,
                    'last_activity_at' => $session->last_activity_at,
                    'logout_at' => $session->logout_at,
                ];
            });

        $users = User::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/audit/sessions/index', [
            'sessions' => $sessions,
            'filters' => $filters + ['perPage' => $perPage],
            'options' => [
                'users' => $users,
            ],
        ]);
    }
}
