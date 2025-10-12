<?php

namespace App\Listeners;

use App\Services\DashboardStatsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClearWidgetCacheOnUserChange implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private DashboardStatsService $statsService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        // Limpiar caché de widgets cuando se registra un nuevo usuario
        $this->statsService->clearCache();
    }
}
