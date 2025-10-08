<?php

namespace Like\Fcv\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Like\Fcv\Models\Course;
use Like\Fcv\Services\CourseStatsService;

class CourseApiController extends Controller
{
    public function __construct(
        protected CourseStatsService $statsService
    ) {}

    /**
     * Obtener estadísticas de un curso
     */
    public function stats(string $id): JsonResponse
    {
        $course = Course::withTrashed()->findOrFail($id);
        
        $stats = $this->statsService->getCourseStats($course);
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Actualizar horarios de un curso
     */
    public function updateSchedules(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.day_of_week' => 'required|integer|between:0,6',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
        ]);

        $course = Course::findOrFail($id);
        
        // Eliminar horarios existentes
        $course->schedules()->delete();
        
        // Crear nuevos horarios
        foreach ($validated['schedules'] as $schedule) {
            $course->schedules()->create([
                'day_of_week' => $schedule['day_of_week'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Horarios actualizados correctamente',
            'data' => $course->load('schedules')
        ]);
    }

    /**
     * Restaurar un curso eliminado
     */
    public function restore(string $id): JsonResponse
    {
        $course = Course::withTrashed()->findOrFail($id);
        $course->restore();
        
        return response()->json([
            'success' => true,
            'message' => 'Curso restaurado correctamente',
            'data' => $course
        ]);
    }
}
