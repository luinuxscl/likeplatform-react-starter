<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Servicio para gestionar menús dinámicos desde packages
 */
class MenuService
{
    /**
     * Cache key para menús compilados
     */
    private const CACHE_KEY = 'customization_menus_compiled';

    /**
     * Cache TTL en segundos (1 hora)
     */
    private const CACHE_TTL = 3600;

    public function __construct(
        private PackageDiscoveryService $discoveryService
    ) {}

    /**
     * Obtiene todos los items de menú de packages habilitados
     *
     * @return array{platform: array, admin: array, operation: array}
     */
    public function getMenuItems(bool $useCache = true): array
    {
        if ($useCache) {
            $cached = Cache::get(self::CACHE_KEY);
            if ($cached !== null) {
                return $cached;
            }
        }

        $menus = $this->compileMenus();

        Cache::put(self::CACHE_KEY, $menus, self::CACHE_TTL);

        return $menus;
    }

    /**
     * Compila todos los menús de los packages habilitados
     *
     * @return array{platform: array, admin: array, operation: array}
     */
    private function compileMenus(): array
    {
        $packages = $this->discoveryService->getEnabledPackages();

        $menus = [
            'platform' => [],
            'admin' => [],
            'operation' => [],
        ];

        foreach ($packages as $package) {
            $items = $package->getMenuItems();

            foreach ($items as $item) {
                $section = $item['section'] ?? 'operation';

                // Validar que la sección existe
                if (! isset($menus[$section])) {
                    $section = 'operation';
                }

                // Transformar item al formato esperado por el frontend
                $menuItem = [
                    'title' => $item['label'],
                    'href' => $this->resolveRoute($item['route']),
                    'icon' => $item['icon'] ?? null,
                    'permission' => $item['permission'] ?? null,
                    'order' => $item['order'] ?? 100,
                    'active' => $item['active'] ?? true,
                ];

                $menus[$section][] = $menuItem;
            }
        }

        // Ordenar items por el campo 'order'
        foreach ($menus as $section => $items) {
            usort($menus[$section], fn ($a, $b) => $a['order'] <=> $b['order']);
        }

        return $menus;
    }

    /**
     * Resuelve una ruta nombrada a su URL
     */
    private function resolveRoute(string $route): string
    {
        // Si ya es una URL completa, retornarla tal cual
        if (str_starts_with($route, 'http://') || str_starts_with($route, 'https://') || str_starts_with($route, '/')) {
            return $route;
        }

        // Intentar resolver como ruta nombrada
        if (Route::has($route)) {
            return route($route);
        }

        // Fallback: asumir que es un path relativo
        return '/'.$route;
    }

    /**
     * Filtra items de menú según permisos del usuario
     */
    public function filterByPermissions(array $items, ?array $userPermissions = null): array
    {
        if ($userPermissions === null) {
            $userPermissions = auth()->user()?->getAllPermissions()?->pluck('name')?->toArray() ?? [];
        }

        return array_filter($items, function ($item) use ($userPermissions) {
            // Si no requiere permiso, siempre mostrar
            if (! isset($item['permission']) || $item['permission'] === null) {
                return true;
            }

            // Verificar si el usuario tiene el permiso
            return in_array($item['permission'], $userPermissions, true);
        });
    }

    /**
     * Limpia la caché de menús
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Obtiene items de menú para una sección específica
     */
    public function getMenuItemsForSection(string $section, bool $filterPermissions = true): array
    {
        $menus = $this->getMenuItems();
        $items = $menus[$section] ?? [];

        if ($filterPermissions) {
            $items = $this->filterByPermissions($items);
        }

        return $items;
    }
}
