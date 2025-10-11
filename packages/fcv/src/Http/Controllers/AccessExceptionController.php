<?php

namespace Like\Fcv\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Like\Fcv\Models\AccessException;
use Like\Fcv\Models\Person;

class AccessExceptionController extends Controller
{
    /**
     * Lista de excepciones
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'person_id' => ['sometimes', 'integer', 'exists:fcv_persons,id'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'approved', 'rejected'])],
            'reason' => ['sometimes', 'string', Rule::in(['medical', 'special_event', 'maintenance', 'administrative', 'other'])],
            'active_only' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = AccessException::query()
            ->with(['person:id,rut,name', 'creator:id,name', 'approver:id,name'])
            ->orderByDesc('created_at');

        if (isset($filters['person_id'])) {
            $query->forPerson($filters['person_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['reason'])) {
            $query->where('reason', $filters['reason']);
        }

        if (isset($filters['active_only']) && $filters['active_only']) {
            $query->active();
        }

        $perPage = $filters['per_page'] ?? 15;
        $exceptions = $query->paginate($perPage);

        return response()->json($exceptions);
    }

    /**
     * Crear nueva excepción
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'person_id' => ['required', 'integer', 'exists:fcv_persons,id'],
            'reason' => ['required', 'string', Rule::in(['medical', 'special_event', 'maintenance', 'administrative', 'other'])],
            'description' => ['required', 'string', 'max:1000'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
        ]);

        $exception = AccessException::create([
            'person_id' => $data['person_id'],
            'reason' => $data['reason'],
            'description' => $data['description'],
            'valid_from' => Carbon::parse($data['valid_from']),
            'valid_until' => Carbon::parse($data['valid_until']),
            'created_by' => $request->user()->id,
            'status' => 'pending',
        ]);

        $exception->load(['person:id,rut,name', 'creator:id,name']);

        return response()->json([
            'message' => 'Excepción creada exitosamente',
            'exception' => $exception,
        ], 201);
    }

    /**
     * Ver detalle de excepción
     */
    public function show(AccessException $exception): JsonResponse
    {
        $exception->load(['person:id,rut,name', 'creator:id,name', 'approver:id,name']);

        return response()->json($exception);
    }

    /**
     * Actualizar excepción (solo si está pendiente)
     */
    public function update(Request $request, AccessException $exception): JsonResponse
    {
        if (!$exception->isPending()) {
            return response()->json([
                'message' => 'Solo se pueden editar excepciones pendientes',
            ], 422);
        }

        $data = $request->validate([
            'reason' => ['sometimes', 'string', Rule::in(['medical', 'special_event', 'maintenance', 'administrative', 'other'])],
            'description' => ['sometimes', 'string', 'max:1000'],
            'valid_from' => ['sometimes', 'date'],
            'valid_until' => ['sometimes', 'date', 'after:valid_from'],
        ]);

        if (isset($data['valid_from'])) {
            $data['valid_from'] = Carbon::parse($data['valid_from']);
        }

        if (isset($data['valid_until'])) {
            $data['valid_until'] = Carbon::parse($data['valid_until']);
        }

        $exception->update($data);
        $exception->load(['person:id,rut,name', 'creator:id,name']);

        return response()->json([
            'message' => 'Excepción actualizada exitosamente',
            'exception' => $exception,
        ]);
    }

    /**
     * Eliminar excepción (solo si está pendiente)
     */
    public function destroy(AccessException $exception): JsonResponse
    {
        if (!$exception->isPending()) {
            return response()->json([
                'message' => 'Solo se pueden eliminar excepciones pendientes',
            ], 422);
        }

        $exception->delete();

        return response()->json([
            'message' => 'Excepción eliminada exitosamente',
        ]);
    }

    /**
     * Aprobar excepción
     */
    public function approve(Request $request, AccessException $exception): JsonResponse
    {
        if (!$exception->isPending()) {
            return response()->json([
                'message' => 'Solo se pueden aprobar excepciones pendientes',
            ], 422);
        }

        $exception->approve($request->user());
        $exception->load(['person:id,rut,name', 'creator:id,name', 'approver:id,name']);

        return response()->json([
            'message' => 'Excepción aprobada exitosamente',
            'exception' => $exception,
        ]);
    }

    /**
     * Rechazar excepción
     */
    public function reject(Request $request, AccessException $exception): JsonResponse
    {
        if (!$exception->isPending()) {
            return response()->json([
                'message' => 'Solo se pueden rechazar excepciones pendientes',
            ], 422);
        }

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $exception->reject($request->user(), $data['rejection_reason']);
        $exception->load(['person:id,rut,name', 'creator:id,name', 'approver:id,name']);

        return response()->json([
            'message' => 'Excepción rechazada',
            'exception' => $exception,
        ]);
    }

    /**
     * Obtener excepciones activas de una persona
     */
    public function activeForPerson(int $personId): JsonResponse
    {
        $person = Person::findOrFail($personId);

        $activeExceptions = AccessException::query()
            ->forPerson($personId)
            ->active()
            ->with(['creator:id,name', 'approver:id,name'])
            ->get();

        return response()->json([
            'person' => $person->only(['id', 'rut', 'name']),
            'active_exceptions' => $activeExceptions,
        ]);
    }
}
