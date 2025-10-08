<?php

namespace App\Console\Commands;

use App\Services\MenuService;
use App\Services\PackageDiscoveryService;
use Illuminate\Console\Command;

/**
 * Comando para limpiar la caché del sistema de personalización
 */
class CustomizationClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'customization:clear-cache';

    /**
     * The console command description.
     */
    protected $description = 'Limpia la caché de packages y menús descubiertos';

    /**
     * Execute the console command.
     */
    public function handle(
        PackageDiscoveryService $discoveryService,
        MenuService $menuService
    ): int {
        $this->info('Limpiando caché del sistema de personalización...');

        $discoveryService->clearCache();
        $menuService->clearCache();

        $this->info('✅ Caché limpiada exitosamente');

        return self::SUCCESS;
    }
}
