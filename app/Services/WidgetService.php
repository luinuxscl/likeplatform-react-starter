<?php

namespace App\Services;

use App\Contracts\WidgetablePackageInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

/**
 * Servicio para gestionar widgets del dashboard
 */
class WidgetService
{
    /**
     * Cache key para widgets compilados
     */
    private const CACHE_KEY_PREFIX = 'widgets_compiled';

    /**
     * Cache TTL en segundos (1 hora)
     */
    private const CACHE_TTL = 3600;

    public function __construct(
        private PackageDiscoveryService $packageDiscovery
    ) {}

    /**
     * Descubre y compila todos los widgets disponibles
     *
     * @return array<int, array{
     *     key: string,
     *     component: string,
     *     title: string,
     *     description?: string,
     *     permission?: string,
     *     size: string,
     *     refresh_interval?: int,
     *     endpoint?: string,
     *     config?: array<string, mixed>,
     *     order?: int,
     *     active?: bool,
     *     package?: string
     * }>
     */
    public function discover(bool $useCache = true): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX.'_all';

        if ($useCache && config('widgets.cache.enabled', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $widgets = [];

        // 1. Cargar widgets del core
        $coreWidgets = config('widgets.widgets', []);
        foreach ($coreWidgets as $widget) {
            if ($this->isValidWidget($widget)) {
                $widget['package'] = 'core';
                $widgets[] = $widget;
            }
        }

        // 2. Descubrir widgets de packages
        if (config('widgets.auto_discovery.enabled', true)) {
            $packages = $this->packageDiscovery->getEnabledPackages();

            foreach ($packages as $package) {
                if ($package instanceof WidgetablePackageInterface && $package->hasWidgets()) {
                    $packageWidgets = $package->getWidgets();

                    foreach ($packageWidgets as $widget) {
                        if ($this->isValidWidget($widget)) {
                            $widget['package'] = $package->getName();
                            $widgets[] = $widget;
                        }
                    }
                }
            }
        }

        // 3. Ordenar por order (si existe)
        usort($widgets, fn($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        // Cachear resultado
        if (config('widgets.cache.enabled', true)) {
            $ttl = config('widgets.cache.ttl', self::CACHE_TTL);
            Cache::put($cacheKey, $widgets, $ttl);
        }

        return $widgets;
    }

    /**
     * Obtiene widgets disponibles para el usuario actual
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @return array<int, array>
     */
    public function getForUser($user = null): array
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return [];
        }

        $cacheKey = self::CACHE_KEY_PREFIX.'_user_'.$user->id;

        if (config('widgets.cache.enabled', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $allWidgets = $this->discover();
        $availableWidgets = [];

        foreach ($allWidgets as $widget) {
            // Verificar si está activo
            if (isset($widget['active']) && !$widget['active']) {
                continue;
            }

            // Verificar permisos
            if (isset($widget['permission']) && !empty($widget['permission'])) {
                if (!$user->can($widget['permission'])) {
                    continue;
                }
            }

            $availableWidgets[] = $widget;
        }

        // Cachear resultado
        if (config('widgets.cache.enabled', true)) {
            $ttl = config('widgets.cache.ttl', self::CACHE_TTL);
            Cache::put($cacheKey, $availableWidgets, $ttl);
        }

        return $availableWidgets;
    }

    /**
     * Obtiene un widget específico por su key
     *
     * @param  string  $key
     * @return array|null
     */
    public function getWidget(string $key): ?array
    {
        $widgets = $this->discover();

        foreach ($widgets as $widget) {
            if ($widget['key'] === $key) {
                return $widget;
            }
        }

        return null;
    }

    /**
     * Verifica si un widget es válido
     *
     * @param  array  $widget
     */
    private function isValidWidget(array $widget): bool
    {
        // Campos requeridos
        $required = ['key', 'component', 'title', 'size'];

        foreach ($required as $field) {
            if (!isset($widget[$field]) || empty($widget[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Limpia la caché de widgets
     */
    public function clearCache(?int $userId = null): void
    {
        if ($userId !== null) {
            // Limpiar caché de un usuario específico
            Cache::forget(self::CACHE_KEY_PREFIX.'_user_'.$userId);
        } else {
            // Limpiar toda la caché de widgets
            Cache::forget(self::CACHE_KEY_PREFIX.'_all');
            
            // También limpiar caché de usuarios (pattern matching)
            // Nota: Esto depende del driver de caché usado
            if (method_exists(Cache::getStore(), 'flush')) {
                // Para drivers que soportan flush selectivo
                $pattern = self::CACHE_KEY_PREFIX.'_user_*';
                // Implementación básica, mejorar según driver
            }
        }
    }

    /**
     * Obtiene widgets agrupados por package
     *
     * @return array<string, array>
     */
    public function getGroupedByPackage(): array
    {
        $widgets = $this->discover();
        $grouped = [];

        foreach ($widgets as $widget) {
            $package = $widget['package'] ?? 'core';
            
            if (!isset($grouped[$package])) {
                $grouped[$package] = [];
            }

            $grouped[$package][] = $widget;
        }

        return $grouped;
    }

    /**
     * Valida la configuración de refresh interval
     *
     * @param  int  $interval
     */
    public function validateRefreshInterval(int $interval): bool
    {
        $min = config('widgets.refresh.min_interval', 60);
        $max = config('widgets.refresh.max_interval', 3600);

        return $interval >= $min && $interval <= $max;
    }

    /**
     * Obtiene el intervalo de refresh por defecto
     */
    public function getDefaultRefreshInterval(): int
    {
        return config('widgets.refresh.default_interval', 300);
    }

    /**
     * Notifica a un package sobre el refresh de un widget
     *
     * @param  string  $widgetKey
     * @param  array  $data
     */
    public function notifyWidgetRefresh(string $widgetKey, array $data): void
    {
        $widget = $this->getWidget($widgetKey);

        if (!$widget || !isset($widget['package'])) {
            return;
        }

        $package = $this->packageDiscovery->getPackage($widget['package']);

        if ($package instanceof WidgetablePackageInterface) {
            $package->onWidgetRefresh($widgetKey, $data);
        }
    }

    /**
     * Notifica a un package sobre cambios en la configuración de un widget
     *
     * @param  string  $widgetKey
     * @param  array  $config
     */
    public function notifyWidgetConfigUpdated(string $widgetKey, array $config): void
    {
        $widget = $this->getWidget($widgetKey);

        if (!$widget || !isset($widget['package'])) {
            return;
        }

        $package = $this->packageDiscovery->getPackage($widget['package']);

        if ($package instanceof WidgetablePackageInterface) {
            $package->onWidgetConfigUpdated($widgetKey, $config);
        }
    }
}
