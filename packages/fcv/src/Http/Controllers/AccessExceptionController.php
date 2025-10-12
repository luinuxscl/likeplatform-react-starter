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
     * Lista de excepciones (Inertia)
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return $this->indexJson($request);
        }

        $query = AccessException::query()
            ->with(['person:id,rut,name', 'creator:id,name', 'approver:id,name'])
            ->orderByDesc('created_at');

        $exceptions = $query->paginate(15);

        return inertia('FCV/Exceptions/Index', [
            'exceptions' => $exceptions,
        ]);
    }

    /**
     * Lista de excepciones (JSON)
     */
    protected function indexJson(Request $request): JsonResponse
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
     * Mostrar formulario de creación
     */
    public function create()
    {
        $persons = Person::query()
            ->select('id', 'rut', 'name')
            ->orderBy('name')
            ->get();

        return inertia('FCV/Exceptions/Create', [
            'persons' => $persons,
        ]);
    }

    /**
     * Crear nueva excepción
     */
    public function store(Request $request)
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

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Excepción creada exitosamente',
                'exception' => $exception,
            ], 201);
        }

        return redirect()->route('fcv.access-exceptions.index')
            ->with('success', 'Excepción creada exitosamente');
    }

    /**
     * Ver detalle de excepción
     */
    public function show(AccessException $exception)
    {
        $exception->load(['person:id,rut,name', 'creator:id,name', 'approver:id,name']);

        if (request()->wantsJson()) {
            return response()->json($exception);
        }

        return inertia('FCV/Exceptions/Show', [
            'exception' => $exception,
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(AccessException $exception)
    {
        if (! $exception->isPending()) {
            return redirect()->route('fcv.access-exceptions.index')
                ->with('error', 'Solo se pueden editar excepciones pendientes');
        }

        $exception->load(['person:id,rut,name']);

        return inertia('FCV/Exceptions/Edit', [
            'exception' => $exception,
        ]);
    }

    /**
     * Ver detalle de excepción (JSON)
     */
    protected function showJson(AccessException $exception): JsonResponse
    {
        $exception->load(['person:id,rut,name', 'creator:id,name', 'approver:id,name']);

        return response()->json($exception);
    }

    /**
     * Actualizar excepción (solo si está pendiente)
     */
    public function update(Request $request, AccessException $exception)
    {
        if (! $exception->isPending()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Solo se pueden editar excepciones pendientes',
                ], 422);
            }

            return redirect()->route('fcv.access-exceptions.index')
                ->with('error', 'Solo se pueden editar excepciones pendientes');
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

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Excepción actualizada exitosamente',
                'exception' => $exception,
            ]);
        }

        return redirect()->route('fcv.access-exceptions.index')
            ->with('success', 'Excepción actualizada exitosamente');
    }

    /**
     * Eliminar excepción (solo si está pendiente)
     */
    public function destroy(AccessException $exception)
    {
        if (! $exception->isPending()) {
            if (request()->wantsJson()) {
                return response()->json([
                    'message' => 'Solo se pueden eliminar excepciones pendientes',
                ], 422);
            }

            return redirect()->route('fcv.access-exceptions.index')
                ->with('error', 'Solo se pueden eliminar excepciones pendientes');
        }

        $exception->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Excepción eliminada exitosamente',
            ]);
        }

        return redirect()->route('fcv.access-exceptions.index')
            ->with('success', 'Excepción eliminada exitosamente');
    }

    /**
     * Aprobar excepción
     */
    public function approve(Request $request, AccessException $exception)
    {
        if (! $exception->isPending()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Solo se pueden aprobar excepciones pendientes',
                ], 422);
            }

            return redirect()->route('fcv.access-exceptions.index')
                ->with('error', 'Solo se pueden aprobar excepciones pendientes');
        }

        $exception->approve($request->user());
        $exception->load(['person:id,rut,name', 'creator:id,name', 'approver:id,name']);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Excepción aprobada exitosamente',
                'exception' => $exception,
            ]);
        }

        return redirect()->route('fcv.access-exceptions.index')
            ->with('success', 'Excepción aprobada exitosamente');
    }

    /**
     * Rechazar excepción
     */
    public function reject(Request $request, AccessException $exception)
    {
        if (! $exception->isPending()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Solo se pueden rechazar excepciones pendientes',
                ], 422);
            }

            return redirect()->route('fcv.access-exceptions.index')
                ->with('error', 'Solo se pueden rechazar excepciones pendientes');
        }

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $exception->reject($request->user(), $data['rejection_reason']);
        $exception->load(['person:id,rut,name', 'creator:id,name', 'approver:id,name']);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Excepción rechazada',
                'exception' => $exception,
            ]);
        }

        return redirect()->route('fcv.access-exceptions.index')
            ->with('success', 'Excepción rechazada');
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
