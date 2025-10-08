<?php

namespace Like\Fcv\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Inertia\Inertia;
use Like\Fcv\Console\Commands\InstallCommand;

class FcvServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/fcv.php', 'fcv');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        // Cargar rutas
        $this->loadRoutesFrom(__DIR__.'/../../routes/fcv.php');
        
        // Cargar migraciones
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        
        // Cargar traducciones
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'fcv');
        
        // Cargar vistas Blade (si existen)
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'fcv');
        
        // Publicar configuración
        $this->publishes([
            __DIR__.'/../../config/fcv.php' => config_path('fcv.php'),
        ], 'fcv-config');

        // Publicar vistas Blade (si existen)
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/fcv'),
        ], 'fcv-views');

        // Publicar assets compilados (si es necesario)
        $this->publishes([
            __DIR__.'/../../public' => public_path('vendor/fcv'),
        ], 'fcv-assets');

        // Publicar componentes React/Inertia
        $this->publishes([
            __DIR__.'/../../resources/js' => resource_path('js/vendor/fcv'),
        ], 'fcv-js');

        // Registrar componentes de Blade
        $this->registerBladeComponents();

        // Registrar componentes de Inertia
        $this->registerInertiaComponents();

        // Los menús ahora se gestionan a través del sistema de packages
        // Ver: packages/fcv/config/menu.php
        // El sistema de auto-discovery los carga automáticamente
    }

    /**
     * Registrar componentes de Blade
     */
    protected function registerBladeComponents(): void
    {
        // Ejemplo de registro de un componente de Blade
        // Blade::component('fcv-alert', Alert::class);
    }

    /**
     * Registrar componentes de Inertia
     */
    protected function registerInertiaComponents(): void
    {
        // Aquí podrías registrar componentes de Inertia si es necesario
        // Por ejemplo, para usar con Inertia::render()
    }
}
