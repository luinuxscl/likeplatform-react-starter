<?php

namespace App\Providers;

use App\Services\MenuService;
use App\Services\PackageDiscoveryService;
use App\Services\ThemeService;
use App\Support\ThemeCompiler;
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
        $this->app->singleton(ThemeCompiler::class);
        $this->app->singleton(ThemeService::class);

        // Merge configuración
        $this->mergeConfigFrom(
            __DIR__.'/../../config/customization.php',
            'customization'
        );
        
        $this->mergeConfigFrom(
            __DIR__.'/../../config/themes.php',
            'themes'
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
        
        $this->publishes([
            __DIR__.'/../../config/themes.php' => config_path('themes.php'),
        ], 'themes-config');

        // Descubrir y registrar packages
        $this->discoverAndRegisterPackages();

        // Compartir menús con Inertia
        $this->shareMenusWithInertia();
        
        // Compartir themes con Inertia
        $this->shareThemesWithInertia();
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

    /**
     * Comparte los themes de packages con Inertia
     */
    private function shareThemesWithInertia(): void
    {
        Inertia::share('themes', function () {
            $themeService = $this->app->make(ThemeService::class);

            return $themeService->getThemesForFrontend();
        });
    }
}
