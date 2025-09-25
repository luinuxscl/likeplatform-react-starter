<?php

namespace Like\Fcv\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Like\Fcv\Models\AccessLog;
use Like\Fcv\Models\Person;

class AccessQueryController extends Controller
{
    public function recent(Request $request): JsonResponse
    {
        $limit = (int) $request->integer('limit', 20);
        $limit = max(1, min(100, $limit));

        $logs = AccessLog::query()
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get()
            ->map(function (AccessLog $log) {
                $person = $log->person()->select(['id', 'name', 'rut'])->first();
                return [
                    'id' => $log->id,
                    'occurred_at' => $log->occurred_at?->toIso8601String(),
                    'direction' => $log->direction,
                    'status' => $log->status,
                    'reason' => $log->reason,
                    'person' => $person ? [
                        'id' => $person->id,
                        'name' => $person->name,
                        'rut' => $person->rut,
                    ] : null,
                ];
            });

        return response()->json(['items' => $logs]);
    }
}
