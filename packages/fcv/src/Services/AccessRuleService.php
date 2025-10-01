<?php

namespace Like\Fcv\Services;

use Illuminate\Support\Carbon;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Membership;
use Like\Fcv\Models\Organization;
use Like\Fcv\Models\Person;

class AccessRuleService
{
    private const EARLY_ENTRY_MARGIN_MINUTES = 60;

    public function check(string $rut): array
    {
        $normalized = $this->normalizeRut($rut);
        /** @var Person|null $person */
        $person = Person::query()
            ->with(['memberships.organization', 'courses.schedules'])
            ->where('rut', $normalized)
            ->first();

        if (! $person) {
            return [
                'allowed' => false,
                'status' => 'denegado',
                'reason' => 'Persona no registrada',
                'person' => null,
            ];
        }

        $now = Carbon::now();
        $today = Carbon::today();

        $activeMembers = $person->memberships
            ->filter(function (Membership $membership) use ($today) {
                $startsBefore = $membership->start_date === null || Carbon::parse($membership->start_date)->lte($today);
                $endsAfter = $membership->end_date === null || Carbon::parse($membership->end_date)->gte($today);

                return $startsBefore && $endsAfter;
            });

        // Cursos vigentes (por fecha y sin soft delete)
        $activeCourses = $person->courses
            ->filter(function (Course $course) use ($today) {
                $withinFrom = $course->valid_from === null || $course->valid_from->lte($today);
                $withinUntil = $course->valid_until === null || $course->valid_until->gte($today);

                return $withinFrom && $withinUntil;
            });

        $basePayload = [
            'allowed' => false,
            'status' => 'denegado',
            'reason' => 'Sin membresías activas',
            'person' => $person->only(['id', 'rut', 'name']),
        ];

        foreach ($activeMembers as $membership) {
            $organization = $membership->organization;
            if (! $organization instanceof Organization) {
                continue;
            }

            if ($membership->role === 'funcionario') {
                return [
                    'allowed' => true,
                    'status' => 'permitido',
                    'reason' => 'Acceso de funcionario',
                    'person' => $person->only(['id', 'rut', 'name']),
                    'organization' => $this->organizationPayload($organization),
                ];
            }

            if ($membership->role !== 'alumno') {
                continue;
            }

            $coursesForOrg = $activeCourses->where('organization_id', $organization->id);

            if ($coursesForOrg->isEmpty()) {
                $basePayload = [
                    'allowed' => false,
                    'status' => 'denegado',
                    'reason' => 'Alumno sin curso vigente',
                    'person' => $person->only(['id', 'rut', 'name']),
                    'organization' => $this->organizationPayload($organization),
                ];

                continue;
            }

            foreach ($coursesForOrg as $course) {
                $toleranceMinutes = $this->resolveToleranceMinutes($course);

                if ($this->isWithinCourseSchedule($course, $toleranceMinutes, $now)) {
                    return [
                        'allowed' => true,
                        'status' => 'permitido',
                        'reason' => $toleranceMinutes === null
                            ? 'Alumno con acceso sin restricción'
                            : sprintf('Alumno dentro de tolerancia (%d min)', $toleranceMinutes),
                        'person' => $person->only(['id', 'rut', 'name']),
                        'organization' => $this->organizationPayload($organization),
                        'course' => $this->coursePayload($course, $toleranceMinutes),
                    ];
                }
            }

            $firstCourse = $coursesForOrg->first();

            $basePayload = [
                'allowed' => false,
                'status' => 'denegado',
                'reason' => 'Alumno fuera de horario',
                'person' => $person->only(['id', 'rut', 'name']),
                'organization' => $this->organizationPayload($organization),
                'course' => $firstCourse ? $this->coursePayload($firstCourse, $this->resolveToleranceMinutes($firstCourse)) : null,
            ];
        }

        return $basePayload;
    }

    protected function normalizeRut(string $rut): string
    {
        $rut = preg_replace('/[^0-9kK]/', '', $rut) ?? $rut;
        $rut = strtolower($rut);
        return $rut;
    }

    protected function resolveToleranceMinutes(Course $course): ?int
    {
        $mode = (string) ($course->entry_tolerance_mode ?? '20');

        if ($mode === 'none') {
            return null;
        }

        if (is_numeric($mode)) {
            return (int) $mode;
        }

        return $course->entry_tolerance_minutes ?? 20;
    }

    protected function coursePayload(Course $course, ?int $toleranceMinutes): array
    {
        return [
            'id' => $course->id,
            'name' => $course->name,
            'entry_tolerance_mode' => (string) ($course->entry_tolerance_mode ?? ($toleranceMinutes ?? '20')),
            'entry_tolerance_minutes' => $toleranceMinutes,
        ];
    }

    protected function organizationPayload(Organization $organization): array
    {
        return [
            'id' => $organization->id,
            'name' => $organization->name,
            'acronym' => $organization->acronym,
        ];
    }

    protected function isWithinCourseSchedule(Course $course, ?int $toleranceMinutes, Carbon $reference): bool
    {
        if ($toleranceMinutes === null) {
            return true;
        }

        $dayOfWeek = $reference->dayOfWeek;
        $schedules = $course->schedules->where('day_of_week', $dayOfWeek);

        if ($schedules->isEmpty()) {
            // Si no hay horario registrado, permitimos la entrada para evitar bloqueos mientras se configuran
            return true;
        }

        foreach ($schedules as $schedule) {
            $start = Carbon::createFromFormat('H:i:s', $schedule->start_time, $reference->timezone)
                ->setDate($reference->year, $reference->month, $reference->day);

            $windowStart = $start->copy()->subMinutes(self::EARLY_ENTRY_MARGIN_MINUTES);
            $windowEnd = $start->copy()->addMinutes($toleranceMinutes);

            if ($reference->greaterThanOrEqualTo($windowStart) && $reference->lessThanOrEqualTo($windowEnd)) {
                return true;
            }
        }

        return false;
    }
}
