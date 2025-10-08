<?php

namespace Ejemplo\MiModulo;

use Illuminate\Support\ServiceProvider;

/**
 * ServiceProvider del package Mi Módulo
 */
class MiModuloServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuración del package
        $this->mergeConfigFrom(
            __DIR__.'/../config/menu.php',
            'mi-modulo'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar migraciones
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publicar configuración
        $this->publishes([
            __DIR__.'/../config/menu.php' => config_path('mi-modulo.php'),
        ], 'mi-modulo-config');

        // Publicar assets (si los hay)
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/mi-modulo'),
        ], 'mi-modulo-assets');

        // Registrar comandos
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\InstallCommand::class,
            ]);
        }
    }
}
