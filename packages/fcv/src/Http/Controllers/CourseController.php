<?php

namespace Like\Fcv\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Like\Fcv\Http\Requests\CourseRequest;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Organization;
use Like\Fcv\Services\CourseStatsService;

/**
 * Controlador para la gestión de cursos
 */
class CourseController extends Controller
{
    /**
     * @var CourseStatsService
     */
    protected $statsService;

    /**
     * Constructor del controlador
     */
    public function __construct(CourseStatsService $statsService)
    {
        $this->statsService = $statsService;
    }
    /**
     * Muestra la lista de cursos
     *
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        $today = now()->startOfDay();

        $courses = Course::query()
            ->withTrashed()
            ->with([
                'organization:id,name,acronym',
                'schedules:id,scheduleable_id,scheduleable_type,day_of_week,start_time,end_time'
            ])
            ->withCount('students')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Course $course) use ($today) {
                return $this->formatCourseForDisplay($course, $today);
            });

        $organizations = Organization::query()
            ->orderBy('name')
            ->get(['id', 'name', 'acronym']);

        $toleranceOptions = $this->getToleranceOptions();

        return Inertia::render('fcv/courses/index', [
            'courses' => $courses,
            'organizations' => $organizations,
            'now' => now()->toIso8601String(),
            'toleranceOptions' => $toleranceOptions,
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo curso
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('fcv/courses/create', [
            'organizations' => Organization::orderBy('name')->get(['id', 'name', 'acronym']),
            'toleranceOptions' => $this->getToleranceOptions(),
        ]);
    }

    /**
     * Almacena un nuevo curso en la base de datos
     *
     * @param  \Like\Fcv\Http\Requests\CourseRequest  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(CourseRequest $request)
    {
        $data = $request->validated();
        $schedules = $data['schedules'] ?? [];
        unset($data['schedules']);

        try {
            DB::beginTransaction();

            /** @var Course $course */
            $course = Course::query()->create($data);

            // Crear horarios si se proporcionan
            if (!empty($schedules)) {
                $this->createCourseSchedules($course, $schedules);
            }

            DB::commit();

            return $this->successResponse(
                'Curso creado correctamente',
                ['course' => $this->formatCourseForDisplay($course)],
                route('fcv.courses.show', $course->id)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear curso: ' . $e->getMessage());
            
            return $this->errorResponse(
                'Error al crear el curso. Por favor, inténtelo de nuevo.'
            );
        }
    }

    public function update(CourseRequest $request, Course $course)
    {
        $data = $request->validated();
        $schedules = $data['schedules'] ?? [];
        unset($data['schedules']);

        try {
            DB::beginTransaction();

            $course->update($data);
            $course->schedules()->delete();

            if (!empty($schedules)) {
                $this->createCourseSchedules($course, $schedules);
            }

            DB::commit();

            return $this->successResponse(
                'Curso actualizado correctamente',
                ['course' => $this->formatCourseForDisplay($course)],
                route('fcv.courses.show', $course->id)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar curso: ' . $e->getMessage());
            
            return $this->errorResponse(
                'Error al actualizar el curso. Por favor, inténtelo de nuevo.'
            );
        }
    }

    public function destroy(Course $course)
    {
        try {
            $hasStudents = $course->students()->exists();
            $message = '';
            $data = [];

            if ($hasStudents) {
                $course->delete();
                $message = 'Curso archivado (tiene estudiantes inscritos).';
                $data['deleted_at'] = $course->deleted_at->toIso8601String();
            } else {
                $course->forceDelete();
                $message = 'Curso eliminado permanentemente.';
            }

            return $this->successResponse($message, $data, route('fcv.courses.index'));
        } catch (\Exception $e) {
            \Log::error('Error al eliminar curso: ' . $e->getMessage());
            return $this->errorResponse('Error al eliminar el curso. Por favor, inténtelo de nuevo.');
        }
    }

    public function restore(int $courseId)
    {
        try {
            $course = Course::withTrashed()->findOrFail($courseId);
            $course->restore();

            return $this->successResponse(
                'Curso restaurado correctamente',
                ['course' => $this->formatCourseForDisplay($course)],
                route('fcv.courses.show', $course->id)
            );
        } catch (\Exception $e) {
            \Log::error('Error al restaurar curso: ' . $e->getMessage());
            return $this->errorResponse('Error al restaurar el curso. Por favor, inténtelo de nuevo.');
        }
    }

    public function show(Course $course): Response
    {
        $today = now()->startOfDay();

        $course->load([
            'organization:id,name,acronym',
            'schedules:id,scheduleable_id,scheduleable_type,day_of_week,start_time,end_time',
        ]);

        $isWithinRange = ($course->valid_from === null || $course->valid_from->lessThanOrEqualTo($today))
            && ($course->valid_until === null || $course->valid_until->greaterThanOrEqualTo($today));
        $hasSchedules = $course->schedules->isNotEmpty();

        $students = $course->students()
            ->with(['memberships.organization:id,name,acronym'])
            ->orderBy('name')
            ->paginate(50);

        $courseData = [
            'id' => $course->id,
            'organization' => [
                'id' => $course->organization?->id,
                'name' => $course->organization?->name,
                'acronym' => $course->organization?->acronym,
            ],
            'name' => $course->name,
            'code' => $course->code,
            'description' => $course->description,
            'valid_from' => $course->valid_from?->toDateString(),
            'valid_until' => $course->valid_until?->toDateString(),
            'entry_tolerance_mode' => (string) ($course->entry_tolerance_mode ?? '20'),
            'entry_tolerance_minutes' => $course->entry_tolerance_minutes,
            'entry_tolerance_label' => $course->entry_tolerance_mode === 'none'
                ? 'Sin restricción'
                : sprintf('%d minutos', $course->entry_tolerance_minutes ?? 20),
            'schedules' => $course->schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day_of_week' => $schedule->day_of_week,
                    'start_time' => substr($schedule->start_time, 0, 5),
                    'end_time' => substr($schedule->end_time, 0, 5),
                ];
            })->values(),
            'students_count' => $course->students()->count(),
            'is_active' => $course->deleted_at === null && $isWithinRange && $hasSchedules,
            'deleted_at' => $course->deleted_at?->toIso8601String(),
        ];

        $studentsData = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'rut' => $student->rut,
                'status' => $student->status,
                'contact_info' => $student->contact_info,
                'memberships' => $student->memberships->map(function ($membership) {
                    return [
                        'id' => $membership->id,
                        'role' => $membership->role,
                        'organization' => [
                            'id' => $membership->organization->id,
                            'name' => $membership->organization->name,
                            'acronym' => $membership->organization->acronym,
                        ],
                        'start_date' => $membership->start_date,
                        'end_date' => $membership->end_date,
                    ];
                })->values(),
            ];
        });

        return Inertia::render('fcv/courses/show', [
            'course' => $courseData,
            'students' => [
                'data' => $studentsData,
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    public function stats(Course $course)
    {
        try {
            $stats = $this->statsService->getStats($course);
            return $this->successResponse('Estadísticas obtenidas correctamente', $stats);
        } catch (\Exception $e) {
            \Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener las estadísticas del curso.');
        }
    }

    public function updateSchedules(Course $course)
    {
        $validated = request()->validate([
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
        ]);

        try {
            DB::beginTransaction();
            
            $course->schedules()->delete();
            
            if (!empty($validated['schedules'])) {
                $this->createCourseSchedules($course, $validated['schedules']);
            }
            
            DB::commit();
            
            return $this->successResponse(
                'Horarios actualizados correctamente',
                ['course' => $this->formatCourseForDisplay($course)]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar horarios: ' . $e->getMessage());
            return $this->errorResponse('Error al actualizar los horarios del curso.');
        }
    }
    
    /**
     * Formatea un curso para su visualización
     */
    protected function formatCourseForDisplay(Course $course, $today = null): array
    {
        $today = $today ?? now()->startOfDay();
        $isWithinRange = ($course->valid_from === null || $course->valid_from->lessThanOrEqualTo($today))
            && ($course->valid_until === null || $course->valid_until->greaterThanOrEqualTo($today));
        $hasSchedules = $course->schedules->isNotEmpty();

        return [
            'id' => $course->id,
            'organization' => [
                'id' => $course->organization?->id,
                'name' => $course->organization?->name,
                'acronym' => $course->organization?->acronym,
            ],
            'name' => $course->name,
            'code' => $course->code,
            'description' => $course->description,
            'valid_from' => $course->valid_from?->toDateString(),
            'valid_until' => $course->valid_until?->toDateString(),
            'entry_tolerance_mode' => (string) ($course->entry_tolerance_mode ?? '20'),
            'entry_tolerance_minutes' => $course->entry_tolerance_minutes,
            'entry_tolerance_label' => $course->entry_tolerance_mode === 'none'
                ? 'Sin restricción'
                : sprintf('%d minutos', $course->entry_tolerance_minutes ?? 20),
            'schedules' => $course->schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day_of_week' => $schedule->day_of_week,
                    'start_time' => substr($schedule->start_time, 0, 5),
                    'end_time' => substr($schedule->end_time, 0, 5),
                ];
            })->values(),
            'students_count' => $course->students()->count(),
            'is_active' => $course->deleted_at === null && $isWithinRange && $hasSchedules,
            'deleted_at' => $course->deleted_at?->toIso8601String(),
        ];
    }
    
    /**
     * Crea los horarios para un curso
     */
    protected function createCourseSchedules(Course $course, array $schedules): void
    {
        $course->schedules()->createMany(array_map(function (array $schedule) {
            return [
                'day_of_week' => $schedule['day_of_week'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
            ];
        }, $schedules));
    }
    
    /**
     * Obtiene las opciones de tolerancia
     */
    protected function getToleranceOptions(): array
    {
        return [
            ['value' => 'none', 'label' => 'Sin restricción'],
            ['value' => '5', 'label' => '5 minutos'],
            ['value' => '10', 'label' => '10 minutos'],
            ['value' => '15', 'label' => '15 minutos'],
            ['value' => '20', 'label' => '20 minutos'],
            ['value' => '30', 'label' => '30 minutos'],
            ['value' => '60', 'label' => '1 hora'],
            ['value' => '120', 'label' => '2 horas'],
        ];
    }
    
    /**
     * Devuelve una respuesta de éxito estandarizada
     */
    protected function successResponse(string $message, array $data = [], ?string $redirect = null)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
        
        if ($redirect) {
            return redirect($redirect)->with('flash.success', $message);
        }
        
        return response()->json($response);
    }
    
    /**
     * Devuelve una respuesta de error estandarizada
     */
    protected function errorResponse(string $message, int $status = 422, ?string $redirect = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if ($redirect) {
            return redirect($redirect)->with('flash.error', $message);
        }
        
        return response()->json($response, $status);
    }
}
