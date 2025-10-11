<?php

namespace Like\Fcv\Services;

use App\Services\Analytics\AnalyticsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Like\Fcv\Models\AccessLog;
use Like\Fcv\Models\Person;

/**
 * Servicio de analytics específico para FCV
 * Extiende AnalyticsService y agrega funcionalidad específica
 */
class FcvAnalyticsService
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    /**
     * Obtiene estadísticas generales de verificaciones
     */
    public function getVerificationStats(Carbon $from, Carbon $to): array
    {
        return $this->analyticsService->getStats('fcv.access.verification', $from, $to);
    }

    /**
     * Obtiene estadísticas de accesos (entradas/salidas)
     */
    public function getAccessStats(Carbon $from, Carbon $to): array
    {
        $entries = $this->analyticsService->getStats('fcv.access.entry', $from, $to);
        $exits = $this->analyticsService->getStats('fcv.access.exit', $from, $to);

        return [
            'entries' => $entries,
            'exits' => $exits,
            'total' => $entries['total'] + $exits['total'],
        ];
    }

    /**
     * Obtiene tendencias de verificaciones
     */
    public function getVerificationTrends(Carbon $from, Carbon $to, string $groupBy = 'day'): Collection
    {
        return $this->analyticsService->getTrends('fcv.access.verification', $from, $to, $groupBy);
    }

    /**
     * Obtiene razones de denegación más comunes
     */
    public function getDeniedReasons(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return $this->analyticsService->getMetadataDistribution(
            'fcv.access.verification',
            'reason',
            $from,
            $to,
            $limit
        );
    }

    /**
     * Obtiene estadísticas de AccessLog (datos específicos de FCV)
     */
    public function getAccessLogStats(Carbon $from, Carbon $to): array
    {
        $total = AccessLog::query()
            ->between($from, $to)
            ->count();

        $allowed = AccessLog::query()
            ->between($from, $to)
            ->allowed()
            ->count();

        $denied = AccessLog::query()
            ->between($from, $to)
            ->denied()
            ->count();

        $entries = AccessLog::query()
            ->between($from, $to)
            ->entry()
            ->count();

        $exits = AccessLog::query()
            ->between($from, $to)
            ->exit()
            ->count();

        return [
            'total' => $total,
            'allowed' => $allowed,
            'denied' => $denied,
            'entries' => $entries,
            'exits' => $exits,
            'allowed_percentage' => $total > 0 ? round(($allowed / $total) * 100, 2) : 0,
            'denied_percentage' => $total > 0 ? round(($denied / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Obtiene personas con más accesos
     */
    public function getTopAccessedPersons(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return AccessLog::query()
            ->between($from, $to)
            ->whereNotNull('person_id')
            ->select('person_id', DB::raw('COUNT(*) as access_count'))
            ->groupBy('person_id')
            ->orderByDesc('access_count')
            ->limit($limit)
            ->with('person:id,rut,name')
            ->get()
            ->map(fn ($log) => [
                'person' => $log->person ? [
                    'id' => $log->person->id,
                    'rut' => $log->person->rut,
                    'name' => $log->person->name,
                ] : null,
                'access_count' => $log->access_count,
            ]);
    }

    /**
     * Obtiene historial de accesos de una persona
     */
    public function getPersonAccessHistory(int $personId, int $limit = 50): Collection
    {
        return AccessLog::query()
            ->byPerson($personId)
            ->with('gatekeeper:id,name')
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'occurred_at' => $log->occurred_at,
                'direction' => $log->direction,
                'status' => $log->status,
                'reason' => $log->reason,
                'gatekeeper' => $log->gatekeeper ? [
                    'id' => $log->gatekeeper->id,
                    'name' => $log->gatekeeper->name,
                ] : null,
                'meta' => $log->meta,
            ]);
    }

    /**
     * Obtiene distribución de accesos por hora del día
     */
    public function getAccessDistributionByHour(Carbon $from, Carbon $to): array
    {
        $driver = DB::connection()->getDriverName();
        
        // Usar sintaxis específica según el driver
        $hourExpression = match($driver) {
            'sqlite' => "CAST(strftime('%H', occurred_at) AS INTEGER)",
            'mysql', 'mariadb' => 'HOUR(occurred_at)',
            'pgsql' => 'EXTRACT(HOUR FROM occurred_at)',
            default => 'HOUR(occurred_at)',
        };

        $byHour = AccessLog::query()
            ->between($from, $to)
            ->select(
                DB::raw("{$hourExpression} as hour"),
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

        return $hourlyData;
    }

    /**
     * Obtiene distribución de accesos por día de la semana
     */
    public function getAccessDistributionByWeekday(Carbon $from, Carbon $to): array
    {
        $driver = DB::connection()->getDriverName();
        
        // Usar sintaxis específica según el driver
        // SQLite: strftime('%w') retorna 0=Domingo, 1=Lunes, etc.
        // MySQL: DAYOFWEEK() retorna 1=Domingo, 2=Lunes, etc.
        $dayExpression = match($driver) {
            'sqlite' => "CAST(strftime('%w', occurred_at) AS INTEGER)",
            'mysql', 'mariadb' => 'DAYOFWEEK(occurred_at) - 1',
            'pgsql' => 'EXTRACT(DOW FROM occurred_at)',
            default => 'DAYOFWEEK(occurred_at) - 1',
        };

        $byDay = AccessLog::query()
            ->between($from, $to)
            ->select(
                DB::raw("{$dayExpression} as day"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('count', 'day')
            ->toArray();

        $weekdays = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $weekdayData = [];

        for ($i = 0; $i < 7; $i++) {
            $weekdayData[$weekdays[$i]] = $byDay[$i] ?? 0;
        }

        return $weekdayData;
    }

    /**
     * Obtiene reporte completo
     */
    public function getCompleteReport(Carbon $from, Carbon $to): array
    {
        return [
            'period' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
            'access_log_stats' => $this->getAccessLogStats($from, $to),
            'verification_stats' => $this->getVerificationStats($from, $to),
            'top_persons' => $this->getTopAccessedPersons($from, $to, 10),
            'denied_reasons' => $this->getDeniedReasons($from, $to, 10),
            'hourly_distribution' => $this->getAccessDistributionByHour($from, $to),
            'weekday_distribution' => $this->getAccessDistributionByWeekday($from, $to),
        ];
    }

    /**
     * Obtiene reporte diario
     */
    public function getDailyReport(Carbon $date): array
    {
        return $this->analyticsService->getDailyReport('fcv.access', $date);
    }

    /**
     * Obtiene reporte semanal
     */
    public function getWeeklyReport(Carbon $weekStart): array
    {
        return $this->analyticsService->getWeeklyReport('fcv.access', $weekStart);
    }

    /**
     * Obtiene reporte mensual
     */
    public function getMonthlyReport(int $year, int $month): array
    {
        return $this->analyticsService->getMonthlyReport('fcv.access', $year, $month);
    }

    /**
     * Compara dos períodos
     */
    public function comparePeriods(Carbon $period1From, Carbon $period1To, Carbon $period2From, Carbon $period2To): array
    {
        return $this->analyticsService->comparePeriods(
            'fcv.access',
            $period1From,
            $period1To,
            $period2From,
            $period2To
        );
    }
}
