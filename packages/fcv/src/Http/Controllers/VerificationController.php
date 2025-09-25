<?php

namespace Like\Fcv\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Like\Fcv\Services\AccessRuleService;

class VerificationController extends Controller
{
    public function __construct(protected AccessRuleService $service)
    {
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rut' => ['required', 'string', 'max:32'],
        ]);

        $decision = $this->service->check($data['rut']);

        return response()->json($decision);
    }
}
