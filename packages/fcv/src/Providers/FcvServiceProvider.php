<?php

namespace Like\Fcv\Providers;

use Illuminate\Support\ServiceProvider;

class FcvServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/fcv.php', 'fcv');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/fcv.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'fcv');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'fcv');

        $this->publishes([
            __DIR__.'/../../config/fcv.php' => config_path('fcv.php'),
        ], 'fcv-config');

        // Publicar componentes React a la carpeta de Pages del host para que Inertia los resuelva
        $this->publishes([
            __DIR__.'/../../resources/js' => resource_path('js/pages/fcv'),
        ], 'fcv-js');
    }
}
