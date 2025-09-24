<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public function log(string $action, ?Model $model = null, array $oldValues = [], array $newValues = [], array $metadata = []): void
    {
        $exclude = config('audit.exclude', []);
        $old = Arr::except($oldValues, $exclude);
        $new = Arr::except($newValues, $exclude);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'old_values' => ! empty($old) ? $old : null,
            'new_values' => ! empty($new) ? $new : null,
            'metadata' => $metadata ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'url' => Request::fullUrl(),
        ]);
    }
}
