<?php

namespace Like\Fcv\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Like\Fcv\Services\FcvAnalyticsService;

class FcvAnalyticsController extends Controller
{
    public function __construct(protected FcvAnalyticsService $analyticsService) {}

    /**
     * Obtiene estadísticas generales
     */
    public function stats(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();

        $stats = [
            'access_log_stats' => $this->analyticsService->getAccessLogStats($from, $to),
            'verification_stats' => $this->analyticsService->getVerificationStats($from, $to),
            'access_stats' => $this->analyticsService->getAccessStats($from, $to),
        ];

        return response()->json($stats);
    }

    /**
     * Obtiene tendencias
     */
    public function trends(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'group_by' => ['sometimes', 'string', 'in:hour,day,week,month'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $groupBy = $data['group_by'] ?? 'day';

        $trends = $this->analyticsService->getVerificationTrends($from, $to, $groupBy);

        return response()->json([
            'trends' => $trends,
            'group_by' => $groupBy,
        ]);
    }

    /**
     * Obtiene top personas con más accesos
     */
    public function topPersons(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $limit = $data['limit'] ?? 10;

        $topPersons = $this->analyticsService->getTopAccessedPersons($from, $to, $limit);

        return response()->json([
            'top_persons' => $topPersons,
            'limit' => $limit,
        ]);
    }

    /**
     * Obtiene razones de denegación
     */
    public function deniedReasons(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $limit = $data['limit'] ?? 10;

        $reasons = $this->analyticsService->getDeniedReasons($from, $to, $limit);

        return response()->json([
            'denied_reasons' => $reasons,
            'limit' => $limit,
        ]);
    }

    /**
     * Obtiene historial de accesos de una persona
     */
    public function personHistory(Request $request, int $personId): JsonResponse
    {
        $data = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ]);

        $limit = $data['limit'] ?? 50;

        $history = $this->analyticsService->getPersonAccessHistory($personId, $limit);

        return response()->json([
            'person_id' => $personId,
            'history' => $history,
            'limit' => $limit,
        ]);
    }

    /**
     * Obtiene distribución por hora
     */
    public function hourlyDistribution(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();

        $distribution = $this->analyticsService->getAccessDistributionByHour($from, $to);

        return response()->json([
            'hourly_distribution' => $distribution,
        ]);
    }

    /**
     * Obtiene distribución por día de la semana
     */
    public function weekdayDistribution(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();

        $distribution = $this->analyticsService->getAccessDistributionByWeekday($from, $to);

        return response()->json([
            'weekday_distribution' => $distribution,
        ]);
    }

    /**
     * Obtiene reporte completo
     */
    public function completeReport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();

        $report = $this->analyticsService->getCompleteReport($from, $to);

        return response()->json($report);
    }

    /**
     * Obtiene reporte diario
     */
    public function dailyReport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($data['date']);

        $report = $this->analyticsService->getDailyReport($date);

        return response()->json($report);
    }

    /**
     * Obtiene reporte semanal
     */
    public function weeklyReport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'week_start' => ['required', 'date'],
        ]);

        $weekStart = Carbon::parse($data['week_start']);

        $report = $this->analyticsService->getWeeklyReport($weekStart);

        return response()->json($report);
    }

    /**
     * Obtiene reporte mensual
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $report = $this->analyticsService->getMonthlyReport($data['year'], $data['month']);

        return response()->json($report);
    }

    /**
     * Compara dos períodos
     */
    public function comparePeriods(Request $request): JsonResponse
    {
        $data = $request->validate([
            'period1_from' => ['required', 'date'],
            'period1_to' => ['required', 'date', 'after_or_equal:period1_from'],
            'period2_from' => ['required', 'date'],
            'period2_to' => ['required', 'date', 'after_or_equal:period2_from'],
        ]);

        $period1From = Carbon::parse($data['period1_from'])->startOfDay();
        $period1To = Carbon::parse($data['period1_to'])->endOfDay();
        $period2From = Carbon::parse($data['period2_from'])->startOfDay();
        $period2To = Carbon::parse($data['period2_to'])->endOfDay();

        $comparison = $this->analyticsService->comparePeriods(
            $period1From,
            $period1To,
            $period2From,
            $period2To
        );

        return response()->json($comparison);
    }
}
