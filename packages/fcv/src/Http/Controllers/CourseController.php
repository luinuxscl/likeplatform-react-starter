<?php

namespace Like\Fcv\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Like\Fcv\Http\Requests\CourseRequest;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Organization;

class CourseController extends Controller
{
    public function index(): Response
    {
        $today = now()->startOfDay();

        $courses = Course::query()
            ->withTrashed()
            ->with(['organization:id,name,acronym'])
            ->with('schedules:id,scheduleable_id,scheduleable_type,day_of_week,start_time,end_time')
            ->withCount('students')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Course $course) use ($today) {
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
                    'students_count' => $course->students_count,
                    'deleted_at' => $course->deleted_at?->toIso8601String(),
                    'is_active' => $course->deleted_at === null && $isWithinRange && $hasSchedules,
                    'can_hard_delete' => (int) $course->students_count === 0,
                    'can_restore' => $course->trashed(),
                ];
            });

        $organizations = Organization::query()
            ->orderBy('name')
            ->get(['id', 'name', 'acronym']);

        $toleranceOptions = collect(config('fcv.course_entry_tolerance_options', []))
            ->map(fn ($value) => [
                'value' => (string) $value,
                'label' => $value === 'none' ? 'Sin restricción' : sprintf('%s minutos', $value),
            ])
            ->all();

        return Inertia::render('fcv/courses/index', [
            'courses' => $courses,
            'organizations' => $organizations,
            'now' => now()->toIso8601String(),
            'toleranceOptions' => $toleranceOptions,
        ]);
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $schedules = $data['schedules'] ?? [];
        unset($data['schedules']);

        DB::transaction(function () use ($data, $schedules) {
            /** @var Course $course */
            $course = Course::query()->create($data);

            if ($schedules !== []) {
                $course->schedules()->createMany(array_map(function (array $schedule) {
                    return [
                        'day_of_week' => $schedule['day_of_week'],
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                    ];
                }, $schedules));
            }
        });

        return redirect()->route('fcv.courses.index')->with('flash.success', 'Curso creado correctamente.');
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validated();
        $schedules = $data['schedules'] ?? [];
        unset($data['schedules']);

        DB::transaction(function () use ($course, $data, $schedules) {
            $course->update($data);
            $course->schedules()->delete();

            if ($schedules !== []) {
                $course->schedules()->createMany(array_map(function (array $schedule) {
                    return [
                        'day_of_week' => $schedule['day_of_week'],
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                    ];
                }, $schedules));
            }
        });

        return redirect()->route('fcv.courses.index')->with('flash.success', 'Curso actualizado.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $hasStudents = $course->students()->exists();

        if ($hasStudents) {
            $course->delete();

            return redirect()
                ->route('fcv.courses.index')
                ->with('flash.success', 'Curso archivado (tiene estudiantes inscritos).');
        }

        $course->forceDelete();

        return redirect()
            ->route('fcv.courses.index')
            ->with('flash.success', 'Curso eliminado permanentemente.');
    }

    public function restore(int $courseId): RedirectResponse
    {
        $course = Course::withTrashed()->findOrFail($courseId);

        $course->restore();

        return redirect()->route('fcv.courses.index')->with('flash.success', 'Curso restaurado.');
    }
}
