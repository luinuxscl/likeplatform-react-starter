<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogsController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'action' => (string) $request->string('action'),
            'model' => (string) $request->string('model'),
            'user' => $request->integer('user'),
            'date_from' => (string) $request->string('date_from'),
            'date_to' => (string) $request->string('date_to'),
            'search' => (string) $request->string('search'),
        ];
        $perPage = (int) $request->integer('perPage', 15);

        $logs = AuditLog::query()
            ->with('user')
            ->when($filters['action'] !== '', fn ($q) => $q->where('action', $filters['action']))
            ->when($filters['model'] !== '', fn ($q) => $q->where('auditable_type', $filters['model']))
            ->when($filters['user'], fn ($q) => $q->where('user_id', $filters['user']))
            ->when($filters['date_from'] !== '', fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $term = $filters['search'];
                $q->where(function ($inner) use ($term) {
                    $inner->where('url', 'like', "%{$term}%")
                        ->orWhere('ip_address', 'like', "%{$term}%")
                        ->orWhere('user_agent', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (AuditLog $log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'model' => $log->auditable_type ? class_basename($log->auditable_type) : null,
                    'model_full' => $log->auditable_type,
                    'model_id' => $log->auditable_id,
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                    ] : null,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'metadata' => $log->metadata,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'url' => $log->url,
                    'created_at' => $log->created_at,
                ];
            });

        $actions = AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');
        $models = AuditLog::query()->select('auditable_type')->distinct()->pluck('auditable_type')->filter()->map(fn ($m) => [
            'value' => $m,
            'label' => class_basename($m),
        ])->values();
        $users = User::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/audit/logs/index', [
            'logs' => $logs,
            'filters' => $filters + ['perPage' => $perPage],
            'options' => [
                'actions' => $actions,
                'models' => $models,
                'users' => $users,
            ],
        ]);
    }
}
