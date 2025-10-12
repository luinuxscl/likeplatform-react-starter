<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardStatsService
{
    /**
     * Obtener estadísticas generales del dashboard
     */
    public function getStats(): array
    {
        return Cache::remember('dashboard.stats', 300, function () {
            $now = Carbon::now();
            $lastMonth = $now->copy()->subMonth();
            
            // Usuarios
            $currentUsers = User::count();
            $previousUsers = User::where('created_at', '<', $lastMonth)->count();
            $usersChange = $previousUsers > 0 
                ? (($currentUsers - $previousUsers) / $previousUsers) * 100 
                : 0;

            // Actividad (basada en auditorías si existe)
            $currentActivity = $this->getActivityCount($lastMonth, $now);
            $previousActivity = $this->getActivityCount($lastMonth->copy()->subMonth(), $lastMonth);
            $activityChange = $previousActivity > 0 
                ? (($currentActivity - $previousActivity) / $previousActivity) * 100 
                : 0;

            // Revenue (simulado - reemplazar con datos reales si existe tabla de transacciones)
            $revenue = $this->getRevenueData();

            return [
                'revenue' => [
                    'current' => $revenue['current'],
                    'previous' => $revenue['previous'],
                    'change' => $revenue['change'],
                    'trend' => $revenue['trend'],
                ],
                'users' => [
                    'current' => $currentUsers,
                    'previous' => $previousUsers,
                    'change' => round($usersChange, 1),
                    'trend' => $this->getUsersTrend(),
                ],
                'activity' => [
                    'current' => $currentActivity,
                    'previous' => $previousActivity,
                    'change' => round($activityChange, 1),
                    'trend' => $this->getActivityTrend(),
                ],
            ];
        });
    }

    /**
     * Obtener datos de actividad por período
     */
    public function getActivityData(string $period = 'week'): array
    {
        $cacheKey = "dashboard.activity.{$period}";
        
        return Cache::remember($cacheKey, 300, function () use ($period) {
            $days = $period === 'week' ? 7 : 30;
            $data = [];
            
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                
                // Actividad actual
                $currentValue = $this->getActivityForDate($date);
                
                // Actividad de comparación (mismo día del período anterior)
                $comparisonDate = $date->copy()->subDays($days);
                $comparisonValue = $this->getActivityForDate($comparisonDate);
                
                $data[] = [
                    'date' => $period === 'week' ? $date->format('D') : $date->format('M d'),
                    'value' => $currentValue,
                    'comparison' => $comparisonValue,
                ];
            }
            
            return $data;
        });
    }

    /**
     * Obtener datos de objetivos
     */
    public function getGoalsData(int $dailyGoal = 350): array
    {
        return Cache::remember("dashboard.goals.{$dailyGoal}", 300, function () use ($dailyGoal) {
            $data = [];
            $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $value = $this->getActivityForDate($date);
                
                $data[] = [
                    'day' => $weekDays[6 - $i],
                    'value' => $value,
                    'target' => $dailyGoal,
                ];
            }
            
            return $data;
        });
    }

    /**
     * Obtener estadísticas de bienvenida
     */
    public function getWelcomeStats(): array
    {
        return Cache::remember('dashboard.welcome.stats', 300, function () {
            // Acciones rápidas disponibles
            $quickActions = 5;
            
            // Tareas pendientes (si existe sistema de tareas)
            $pending = 0;
            
            // Tareas completadas hoy
            $completed = $this->getCompletedToday();
            
            return [
                'quick_actions' => $quickActions,
                'status' => 'active',
                'pending' => $pending,
                'completed' => $completed,
            ];
        });
    }

    /**
     * Obtener conteo de actividad entre fechas
     */
    private function getActivityCount(Carbon $from, Carbon $to): int
    {
        // Intentar usar tabla de auditorías si existe
        if (DB::getSchemaBuilder()->hasTable('audits')) {
            return DB::table('audits')
                ->whereBetween('created_at', [$from, $to])
                ->count();
        }
        
        // Fallback: contar usuarios creados
        return User::whereBetween('created_at', [$from, $to])->count() * 10;
    }

    /**
     * Obtener actividad para una fecha específica
     */
    private function getActivityForDate(Carbon $date): int
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();
        
        // Intentar usar tabla de auditorías
        if (DB::getSchemaBuilder()->hasTable('audits')) {
            return DB::table('audits')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }
        
        // Fallback: datos simulados basados en usuarios
        $baseActivity = User::count();
        $dayOfWeek = $date->dayOfWeek;
        
        // Variación por día de la semana
        $multipliers = [0.7, 0.9, 1.0, 0.95, 1.1, 0.6, 0.5]; // Dom-Sab
        
        return (int) ($baseActivity * $multipliers[$dayOfWeek] * rand(80, 120) / 100);
    }

    /**
     * Obtener tendencia de usuarios (últimos 12 períodos)
     */
    private function getUsersTrend(): array
    {
        $trend = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = User::where('created_at', '<=', $date)->count();
            $trend[] = $count;
        }
        
        return $trend;
    }

    /**
     * Obtener tendencia de actividad (últimos 12 períodos)
     */
    private function getActivityTrend(): array
    {
        $trend = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = $this->getActivityForDate($date);
            $trend[] = $count;
        }
        
        return $trend;
    }

    /**
     * Obtener datos de revenue (simulado - reemplazar con datos reales)
     */
    private function getRevenueData(): array
    {
        // TODO: Reemplazar con datos reales de tabla de transacciones/ventas
        $userCount = User::count();
        $current = $userCount * 6.47; // Simulado
        $previous = $userCount * 4.32;
        $change = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
        
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $trend[] = (int) ($current * (80 + rand(0, 40)) / 100);
        }
        
        return [
            'current' => round($current, 2),
            'previous' => round($previous, 2),
            'change' => round($change, 1),
            'trend' => $trend,
        ];
    }

    /**
     * Obtener tareas completadas hoy
     */
    private function getCompletedToday(): int
    {
        // TODO: Reemplazar con datos reales si existe sistema de tareas
        if (DB::getSchemaBuilder()->hasTable('audits')) {
            return DB::table('audits')
                ->whereDate('created_at', Carbon::today())
                ->count();
        }
        
        return rand(8, 15);
    }

    /**
     * Limpiar caché de estadísticas
     */
    public function clearCache(): void
    {
        Cache::forget('dashboard.stats');
        Cache::forget('dashboard.activity.week');
        Cache::forget('dashboard.activity.month');
        Cache::forget('dashboard.welcome.stats');
        
        // Limpiar cachés de goals con diferentes valores
        for ($goal = 100; $goal <= 500; $goal += 50) {
            Cache::forget("dashboard.goals.{$goal}");
        }
    }
}
