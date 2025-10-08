<?php

namespace App\Console\Commands;

use App\Services\PackageDiscoveryService;
use Illuminate\Console\Command;

/**
 * Comando para listar packages descubiertos
 */
class CustomizationListPackagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'customization:list-packages';

    /**
     * The console command description.
     */
    protected $description = 'Lista todos los packages de personalización descubiertos';

    /**
     * Execute the console command.
     */
    public function handle(PackageDiscoveryService $discoveryService): int
    {
        $this->info('Descubriendo packages...');
        $this->newLine();

        $packages = $discoveryService->discover(useCache: false);

        if (empty($packages)) {
            $this->warn('No se encontraron packages de personalización.');

            return self::SUCCESS;
        }

        $this->info('Packages descubiertos:');
        $this->newLine();

        $tableData = [];
        foreach ($packages as $package) {
            $tableData[] = [
                $package->getName(),
                $package->getVersion(),
                $package->isEnabled() ? '✅ Habilitado' : '❌ Deshabilitado',
                count($package->getMenuItems()),
                count($package->getRoutes()),
                count($package->getPermissions()),
            ];
        }

        $this->table(
            ['Nombre', 'Versión', 'Estado', 'Menús', 'Rutas', 'Permisos'],
            $tableData
        );

        return self::SUCCESS;
    }
}
