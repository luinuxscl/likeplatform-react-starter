<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $data = Cache::remember('admin:dashboard:kpis', now()->addSeconds(90), function () {
            $totalUsers = User::count();
            $recentUsers = User::query()->orderByDesc('created_at')->limit(5)->get(['id','name','email','created_at','email_verified_at']);
            $newUsers7d = User::where('created_at', '>=', now()->subDays(7))->count();
            $verifiedUsers = User::whereNotNull('email_verified_at')->count();
            $rolesCount = Role::count();

            return [
                'kpis' => [
                    'total_users' => $totalUsers,
                    'new_users_7d' => $newUsers7d,
                    'verified_users' => $verifiedUsers,
                    'roles_count' => $rolesCount,
                ],
                'recent_users' => $recentUsers->map(function (User $u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'email_verified_at' => $u->email_verified_at,
                        'created_at' => $u->created_at,
                    ];
                }),
            ];
        });

        return Inertia::render('admin/dashboard/index', $data);
    }
}
