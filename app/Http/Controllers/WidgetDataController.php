<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetDataController extends Controller
{
    public function __construct(
        private DashboardStatsService $statsService
    ) {}

    /**
     * Obtener datos para StatsWidget
     */
    public function stats(): JsonResponse
    {
        try {
            $data = $this->statsService->getStats();
            
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load statistics',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Obtener datos para ActivityWidget
     */
    public function activity(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'sometimes|in:week,month',
        ]);

        try {
            $period = $request->input('period', 'week');
            $data = $this->statsService->getActivityData($period);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load activity data',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Obtener datos para GoalsWidget
     */
    public function goals(Request $request): JsonResponse
    {
        $request->validate([
            'daily_goal' => 'sometimes|integer|min:50|max:1000',
        ]);

        try {
            $dailyGoal = $request->input('daily_goal', 350);
            $data = $this->statsService->getGoalsData($dailyGoal);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'daily_goal' => $dailyGoal,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load goals data',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Obtener datos para WelcomeWidget
     */
    public function welcome(): JsonResponse
    {
        try {
            $data = $this->statsService->getWelcomeStats();
            
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load welcome data',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Limpiar caché de datos de widgets
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->statsService->clearCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Widget data cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear cache',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
