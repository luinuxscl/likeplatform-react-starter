<?php

namespace App\Providers;

use App\Services\MenuService;
use App\Services\PackageDiscoveryService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

/**
 * ServiceProvider para el sistema de packages de personalización
 */
class CustomizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar servicios como singletons
        $this->app->singleton(PackageDiscoveryService::class);
        $this->app->singleton(MenuService::class);

        // Merge configuración
        $this->mergeConfigFrom(
            __DIR__.'/../../config/customization.php',
            'customization'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publicar configuración
        $this->publishes([
            __DIR__.'/../../config/customization.php' => config_path('customization.php'),
        ], 'customization-config');

        // Descubrir y registrar packages
        $this->discoverAndRegisterPackages();

        // Compartir menús con Inertia
        $this->shareMenusWithInertia();
    }

    /**
     * Descubre y registra todos los packages habilitados
     */
    private function discoverAndRegisterPackages(): void
    {
        $discoveryService = $this->app->make(PackageDiscoveryService::class);
        $packages = $discoveryService->getEnabledPackages();

        foreach ($packages as $package) {
            // Registrar rutas del package
            $this->registerPackageRoutes($package);

            // Registrar permisos (se hará en seeders)
            // Los permisos se registrarán mediante comandos de instalación
        }
    }

    /**
     * Registra las rutas de un package
     */
    private function registerPackageRoutes($package): void
    {
        $routes = $package->getRoutes();

        foreach ($routes as $routeConfig) {
            $method = strtolower($routeConfig['method'] ?? 'get');
            $uri = $routeConfig['uri'] ?? '';
            $action = $routeConfig['action'] ?? null;
            $middleware = $routeConfig['middleware'] ?? ['web', 'auth'];
            $name = $routeConfig['name'] ?? null;

            if (empty($uri) || $action === null) {
                continue;
            }

            $route = Route::$method($uri, $action)
                ->middleware($middleware);

            if ($name !== null) {
                $route->name($name);
            }
        }
    }

    /**
     * Comparte los menús de packages con Inertia
     */
    private function shareMenusWithInertia(): void
    {
        Inertia::share('packages', function () {
            $menuService = $this->app->make(MenuService::class);

            return [
                'menus' => [
                    'platform' => $menuService->getMenuItemsForSection('platform'),
                    'admin' => $menuService->getMenuItemsForSection('admin'),
                    'operation' => $menuService->getMenuItemsForSection('operation'),
                ],
            ];
        });
    }
}
