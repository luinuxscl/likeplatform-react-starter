<?php

namespace App\Services;

use App\Contracts\ThemeablePackageInterface;
use App\Support\ThemeCompiler;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio para gestionar themes de packages
 */
class ThemeService
{
    /**
     * Cache key para themes compilados
     */
    private const CACHE_KEY = 'themes_compiled';

    public function __construct(
        private PackageDiscoveryService $discoveryService,
        private ThemeCompiler $compiler
    ) {}

    /**
     * Obtiene todos los themes de packages habilitados
     *
     * @return array<string, array>
     */
    public function getThemes(bool $useCache = true): array
    {
        if ($useCache && config('themes.cache.enabled')) {
            $cached = Cache::get(self::CACHE_KEY);
            if ($cached !== null) {
                return $cached;
            }
        }

        $themes = $this->compileThemes();

        if (config('themes.cache.enabled')) {
            Cache::put(self::CACHE_KEY, $themes, config('themes.cache.ttl'));
        }

        return $themes;
    }

    /**
     * Compila todos los themes de packages
     */
    private function compileThemes(): array
    {
        $packages = $this->discoveryService->getEnabledPackages();
        $themes = [];

        // Agregar theme base del starterkit
        $themes['starterkit'] = config('themes.starterkit');

        foreach ($packages as $package) {
            if ($package instanceof ThemeablePackageInterface && $package->hasCustomTheme()) {
                $themes[$package->getName()] = $package->getTheme();
            }
        }

        return $themes;
    }

    /**
     * Obtiene el theme de un package específico
     */
    public function getPackageTheme(string $packageName): ?array
    {
        $themes = $this->getThemes();

        return $themes[$packageName] ?? null;
    }

    /**
     * Genera el CSS compilado de todos los themes
     */
    public function getCompiledCss(bool $useCache = true): string
    {
        $cacheKey = self::CACHE_KEY.'_css';

        if ($useCache && config('themes.cache.enabled')) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $themes = $this->getThemes(useCache: false);
        $css = $this->compiler->compileAll($themes);

        if (config('themes.cache.enabled')) {
            Cache::put($cacheKey, $css, config('themes.cache.ttl'));
        }

        return $css;
    }

    /**
     * Obtiene las CSS variables de un package
     */
    public function getPackageVariables(string $packageName, string $mode = 'light'): array
    {
        $theme = $this->getPackageTheme($packageName);

        if (! $theme) {
            return [];
        }

        $compiled = $this->compiler->compile($theme, $packageName);

        return $compiled['variables'][$mode] ?? [];
    }

    /**
     * Limpia la caché de themes
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY.'_css');
    }

    /**
     * Obtiene el modo de theme preferido de un package
     */
    public function getPackageThemeMode(string $packageName): string
    {
        $package = $this->discoveryService->getPackage($packageName);

        if ($package instanceof ThemeablePackageInterface) {
            return $package->getThemeMode();
        }

        return config('themes.default_mode', 'light');
    }

    /**
     * Verifica si un package tiene theme personalizado
     */
    public function hasCustomTheme(string $packageName): bool
    {
        $package = $this->discoveryService->getPackage($packageName);

        if ($package instanceof ThemeablePackageInterface) {
            return $package->hasCustomTheme();
        }

        return false;
    }

    /**
     * Obtiene información de todos los themes para el frontend
     */
    public function getThemesForFrontend(): array
    {
        $themes = $this->getThemes();
        $result = [];

        foreach ($themes as $packageName => $theme) {
            $result[$packageName] = [
                'name' => $theme['name'] ?? $packageName,
                'hasCustomTheme' => $packageName !== 'starterkit',
                'mode' => $this->getPackageThemeMode($packageName),
                'colors' => $theme['colors'] ?? [],
            ];
        }

        return $result;
    }
}
