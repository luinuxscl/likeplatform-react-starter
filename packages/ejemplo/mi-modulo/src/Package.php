<?php

namespace Ejemplo\MiModulo;

use App\Support\CustomizationPackage;

/**
 * Clase principal del package Mi Módulo
 */
class Package extends CustomizationPackage
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'mi-modulo';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function install(): void
    {
        // Ejecutar migraciones
        \Artisan::call('migrate', [
            '--path' => 'packages/ejemplo/mi-modulo/database/migrations',
            '--force' => true,
        ]);

        // Ejecutar seeders de permisos
        \Artisan::call('db:seed', [
            '--class' => 'Ejemplo\\MiModulo\\Database\\Seeders\\PermissionsSeeder',
            '--force' => true,
        ]);

        // Publicar assets si los hay
        \Artisan::call('vendor:publish', [
            '--tag' => 'mi-modulo-assets',
            '--force' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(): void
    {
        // Lógica de desinstalación si es necesaria
        // Por ejemplo: eliminar permisos, limpiar datos, etc.
    }
}
