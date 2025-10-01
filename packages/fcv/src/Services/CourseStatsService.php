<?php

namespace Like\Fcv\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Like\Fcv\Models\Access;
use Like\Fcv\Models\Course;

class CourseStatsService
{
    public function getStats(Course $course): array
    {
        $cacheKey = "course_stats_{$course->id}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($course) {
            $studentsTotal = $course->students()->count();
            $studentsActive = $course->students()
                ->where('status', 'active')
                ->count();

            $now = Carbon::now();
            $sevenDaysAgo = $now->copy()->subDays(7)->startOfDay();
            $thirtyDaysAgo = $now->copy()->subDays(30)->startOfDay();

            // Obtener IDs de estudiantes del curso
            $studentIds = $course->students()->pluck('person_id')->toArray();

            if (empty($studentIds)) {
                return [
                    'students_total' => $studentsTotal,
                    'students_active' => $studentsActive,
                    'attendance_rate_7d' => 0,
                    'attendance_rate_30d' => 0,
                    'access_count_by_day' => [],
                    'top_students' => [],
                    'low_attendance_students' => [],
                ];
            }

            // Accesos últimos 7 días
            $accesses7d = Access::query()
                ->whereIn('person_rut', function ($query) use ($studentIds) {
                    $query->select('rut')
                        ->from('fcv_persons')
                        ->whereIn('id', $studentIds);
                })
                ->where('direction', 'entrada')
                ->where('created_at', '>=', $sevenDaysAgo)
                ->count();

            // Accesos últimos 30 días
            $accesses30d = Access::query()
                ->whereIn('person_rut', function ($query) use ($studentIds) {
                    $query->select('rut')
                        ->from('fcv_persons')
                        ->whereIn('id', $studentIds);
                })
                ->where('direction', 'entrada')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count();

            // Tasa de asistencia (accesos únicos por día / (días * alumnos activos))
            $uniqueDays7d = Access::query()
                ->whereIn('person_rut', function ($query) use ($studentIds) {
                    $query->select('rut')
                        ->from('fcv_persons')
                        ->whereIn('id', $studentIds);
                })
                ->where('direction', 'entrada')
                ->where('created_at', '>=', $sevenDaysAgo)
                ->selectRaw('DATE(created_at) as date')
                ->distinct()
                ->count();

            $attendanceRate7d = $studentsActive > 0 && $uniqueDays7d > 0
                ? round(($accesses7d / ($uniqueDays7d * $studentsActive)) * 100, 2)
                : 0;

            $uniqueDays30d = Access::query()
                ->whereIn('person_rut', function ($query) use ($studentIds) {
                    $query->select('rut')
                        ->from('fcv_persons')
                        ->whereIn('id', $studentIds);
                })
                ->where('direction', 'entrada')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->selectRaw('DATE(created_at) as date')
                ->distinct()
                ->count();

            $attendanceRate30d = $studentsActive > 0 && $uniqueDays30d > 0
                ? round(($accesses30d / ($uniqueDays30d * $studentsActive)) * 100, 2)
                : 0;

            // Accesos por día (últimos 30 días)
            $accessByDay = Access::query()
                ->whereIn('person_rut', function ($query) use ($studentIds) {
                    $query->select('rut')
                        ->from('fcv_persons')
                        ->whereIn('id', $studentIds);
                })
                ->where('direction', 'entrada')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($item) => [
                    'date' => $item->date,
                    'count' => $item->count,
                ])
                ->toArray();

            // Top estudiantes (mayor asistencia últimos 30 días)
            $topStudents = DB::table('fcv_accesses')
                ->join('fcv_persons', 'fcv_accesses.person_rut', '=', 'fcv_persons.rut')
                ->whereIn('fcv_persons.id', $studentIds)
                ->where('fcv_accesses.direction', 'entrada')
                ->where('fcv_accesses.created_at', '>=', $thirtyDaysAgo)
                ->select('fcv_persons.id', 'fcv_persons.name', 'fcv_persons.rut', DB::raw('COUNT(*) as access_count'))
                ->groupBy('fcv_persons.id', 'fcv_persons.name', 'fcv_persons.rut')
                ->orderByDesc('access_count')
                ->limit(10)
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'rut' => $item->rut,
                    'access_count' => $item->access_count,
                ])
                ->toArray();

            // Estudiantes con baja asistencia (menos de 3 accesos en últimos 7 días)
            $lowAttendanceStudents = DB::table('fcv_persons')
                ->leftJoin('fcv_accesses', function ($join) use ($sevenDaysAgo) {
                    $join->on('fcv_persons.rut', '=', 'fcv_accesses.person_rut')
                        ->where('fcv_accesses.direction', 'entrada')
                        ->where('fcv_accesses.created_at', '>=', $sevenDaysAgo);
                })
                ->whereIn('fcv_persons.id', $studentIds)
                ->where('fcv_persons.status', 'active')
                ->select('fcv_persons.id', 'fcv_persons.name', 'fcv_persons.rut', DB::raw('COUNT(fcv_accesses.id) as access_count'))
                ->groupBy('fcv_persons.id', 'fcv_persons.name', 'fcv_persons.rut')
                ->havingRaw('COUNT(fcv_accesses.id) < 3')
                ->orderBy('access_count')
                ->limit(10)
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'rut' => $item->rut,
                    'access_count' => $item->access_count,
                ])
                ->toArray();

            return [
                'students_total' => $studentsTotal,
                'students_active' => $studentsActive,
                'attendance_rate_7d' => $attendanceRate7d,
                'attendance_rate_30d' => $attendanceRate30d,
                'access_count_by_day' => $accessByDay,
                'top_students' => $topStudents,
                'low_attendance_students' => $lowAttendanceStudents,
            ];
        });
    }

    public function clearCache(Course $course): void
    {
        Cache::forget("course_stats_{$course->id}");
    }
}
