<?php

namespace Like\Fcv\Http\Controllers;

use App\Services\Audit\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Like\Fcv\Models\Person;
use Like\Fcv\Services\AccessRuleService;

class VerificationController extends Controller
{
    public function __construct(
        protected AccessRuleService $service,
        protected AuditLogger $auditLogger
    ) {}

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rut' => ['required', 'string', 'max:32'],
        ]);

        $decision = $this->service->check($data['rut']);

        // Registrar verificación en sistema de auditoría
        $person = isset($decision['person']['id'])
            ? Person::find($decision['person']['id'])
            : null;

        $this->auditLogger->log(
            action: 'fcv.access.verification',
            model: $person,
            metadata: [
                'rut' => $data['rut'],
                'allowed' => $decision['allowed'],
                'status' => $decision['status'],
                'reason' => $decision['reason'],
                'organization' => $decision['organization'] ?? null,
                'course' => $decision['course'] ?? null,
            ]
        );

        return response()->json($decision);
    }
}
