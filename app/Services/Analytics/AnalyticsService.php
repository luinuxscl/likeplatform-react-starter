<?php

namespace App\Services\Analytics;

use App\Models\AuditLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio genérico de analytics basado en AuditLog
 * Puede ser usado por cualquier package para obtener estadísticas
 */
class AnalyticsService
{
    /**
     * Obtiene estadísticas generales de una acción
     */
    public function getStats(string $actionPrefix, Carbon $from, Carbon $to): array
    {
        $total = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $byAction = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->select('action', DB::raw('COUNT(*) as count'))
            ->groupBy('action')
            ->get()
            ->pluck('count', 'action')
            ->toArray();

        $byUser = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('user:id,name,email')
            ->get()
            ->map(fn ($log) => [
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
                'count' => $log->count,
            ])
            ->toArray();

        return [
            'total' => $total,
            'by_action' => $byAction,
            'top_users' => $byUser,
            'period' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
        ];
    }

    /**
     * Obtiene tendencias agrupadas por período
     */
    public function getTrends(string $actionPrefix, Carbon $from, Carbon $to, string $groupBy = 'day'): Collection
    {
        $driver = DB::connection()->getDriverName();

        // Formato de fecha según el driver
        $periodExpression = match ($driver) {
            'sqlite' => match ($groupBy) {
                'hour' => "strftime('%Y-%m-%d %H:00:00', created_at)",
                'day' => "strftime('%Y-%m-%d', created_at)",
                'week' => "strftime('%Y-%W', created_at)",
                'month' => "strftime('%Y-%m', created_at)",
                default => "strftime('%Y-%m-%d', created_at)",
            },
            'pgsql' => match ($groupBy) {
                'hour' => "TO_CHAR(created_at, 'YYYY-MM-DD HH24:00:00')",
                'day' => "TO_CHAR(created_at, 'YYYY-MM-DD')",
                'week' => "TO_CHAR(created_at, 'IYYY-IW')",
                'month' => "TO_CHAR(created_at, 'YYYY-MM')",
                default => "TO_CHAR(created_at, 'YYYY-MM-DD')",
            },
            default => match ($groupBy) { // MySQL/MariaDB
                'hour' => "DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')",
                'day' => "DATE_FORMAT(created_at, '%Y-%m-%d')",
                'week' => "DATE_FORMAT(created_at, '%Y-%u')",
                'month' => "DATE_FORMAT(created_at, '%Y-%m')",
                default => "DATE_FORMAT(created_at, '%Y-%m-%d')",
            },
        };

        return AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->select(
                DB::raw("{$periodExpression} as period"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Obtiene los usuarios más activos para una acción
     */
    public function getTopUsers(string $actionPrefix, Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit($limit)
            ->with('user:id,name,email')
            ->get()
            ->map(fn ($log) => [
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
                'count' => $log->count,
            ]);
    }

    /**
     * Obtiene distribución por metadata
     */
    public function getMetadataDistribution(string $actionPrefix, string $metadataKey, Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('metadata')
            ->get()
            ->pluck("metadata.{$metadataKey}")
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take($limit)
            ->map(fn ($count, $value) => [
                'value' => $value,
                'count' => $count,
            ])
            ->values();
    }

    /**
     * Obtiene reporte diario
     */
    public function getDailyReport(string $actionPrefix, Carbon $date): array
    {
        $from = $date->copy()->startOfDay();
        $to = $date->copy()->endOfDay();

        $total = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $byHour = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        // Rellenar horas faltantes con 0
        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[$i] = $byHour[$i] ?? 0;
        }

        return [
            'date' => $date->toDateString(),
            'total' => $total,
            'by_hour' => $hourlyData,
        ];
    }

    /**
     * Obtiene reporte semanal
     */
    public function getWeeklyReport(string $actionPrefix, Carbon $weekStart): array
    {
        $from = $weekStart->copy()->startOfWeek();
        $to = $weekStart->copy()->endOfWeek();

        $total = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $byDay = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('count', 'day')
            ->toArray();

        // Rellenar días faltantes con 0
        $dailyData = [];
        $current = $from->copy();
        while ($current->lte($to)) {
            $dateKey = $current->toDateString();
            $dailyData[$dateKey] = $byDay[$dateKey] ?? 0;
            $current->addDay();
        }

        return [
            'week_start' => $from->toDateString(),
            'week_end' => $to->toDateString(),
            'total' => $total,
            'by_day' => $dailyData,
        ];
    }

    /**
     * Obtiene reporte mensual
     */
    public function getMonthlyReport(string $actionPrefix, int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $total = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $byDay = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$from, $to])
            ->select(
                DB::raw('DAY(created_at) as day'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('count', 'day')
            ->toArray();

        // Rellenar días faltantes con 0
        $dailyData = [];
        $daysInMonth = $from->daysInMonth;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dailyData[$i] = $byDay[$i] ?? 0;
        }

        return [
            'year' => $year,
            'month' => $month,
            'month_name' => $from->format('F'),
            'total' => $total,
            'by_day' => $dailyData,
        ];
    }

    /**
     * Compara dos períodos
     */
    public function comparePeriods(string $actionPrefix, Carbon $period1From, Carbon $period1To, Carbon $period2From, Carbon $period2To): array
    {
        $period1Count = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$period1From, $period1To])
            ->count();

        $period2Count = AuditLog::query()
            ->where('action', 'like', "{$actionPrefix}%")
            ->whereBetween('created_at', [$period2From, $period2To])
            ->count();

        $difference = $period1Count - $period2Count;
        $percentageChange = $period2Count > 0
            ? (($difference / $period2Count) * 100)
            : 0;

        return [
            'period_1' => [
                'from' => $period1From->toIso8601String(),
                'to' => $period1To->toIso8601String(),
                'count' => $period1Count,
            ],
            'period_2' => [
                'from' => $period2From->toIso8601String(),
                'to' => $period2To->toIso8601String(),
                'count' => $period2Count,
            ],
            'difference' => $difference,
            'percentage_change' => round($percentageChange, 2),
        ];
    }
}
