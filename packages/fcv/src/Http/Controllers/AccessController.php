<?php

namespace Like\Fcv\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Like\Fcv\Models\AccessLog;
use Like\Fcv\Models\Person;

class AccessController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rut' => ['nullable', 'string', 'max:32'],
            'direction' => ['required', 'string'], // entrada | salida
            'status' => ['required', 'string'], // permitido | denegado
            'reason' => ['nullable', 'string', 'max:500'],
            'meta' => ['sometimes', 'array'],
        ]);

        $person = null;
        if (! empty($data['rut'])) {
            $rut = strtolower(preg_replace('/[^0-9kK]/', '', $data['rut']) ?? $data['rut']);
            $person = Person::query()->where('rut', $rut)->first();
        }

        $log = AccessLog::query()->create([
            'person_id' => $person?->id,
            'vehicle_id' => null,
            'occurred_at' => now(),
            'direction' => $data['direction'],
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
            'gatekeeper_id' => $request->user()?->id,
            'meta' => array_merge([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], $data['meta'] ?? []),
        ]);

        return response()->json([
            'ok' => true,
            'id' => $log->id,
        ]);
    }
}
