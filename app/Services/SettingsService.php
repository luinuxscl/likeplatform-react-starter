<?php

namespace App\Services;

use App\Contracts\ConfigurablePackageInterface;
use App\Repositories\SettingsRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * Servicio para gestionar settings de packages
 */
class SettingsService
{
    /**
     * Cache key prefix
     */
    private const CACHE_PREFIX = 'package_settings_';

    /**
     * Cache TTL en segundos (1 hora)
     */
    private const CACHE_TTL = 3600;

    public function __construct(
        private PackageDiscoveryService $discoveryService,
        private SettingsRepository $repository
    ) {}

    /**
     * Obtiene todos los packages configurables
     *
     * @return array<string, ConfigurablePackageInterface>
     */
    public function getConfigurablePackages(): array
    {
        $packages = $this->discoveryService->getEnabledPackages();

        return array_filter(
            $packages,
            fn ($package) => $package instanceof ConfigurablePackageInterface && $package->hasSettings()
        );
    }

    /**
     * Obtiene el schema de un package
     */
    public function getPackageSchema(string $packageName): ?array
    {
        $package = $this->discoveryService->getPackage($packageName);

        if (! $package instanceof ConfigurablePackageInterface) {
            return null;
        }

        return $package->getSettingsSchema();
    }

    /**
     * Obtiene los settings de un package (con caché)
     */
    public function getPackageSettings(string $packageName, bool $useCache = true): array
    {
        $cacheKey = self::CACHE_PREFIX.$packageName;

        if ($useCache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        // Obtener settings de la DB
        $settings = $this->repository->getPackageSettings($packageName);

        // Merge con defaults
        $package = $this->discoveryService->getPackage($packageName);
        if ($package instanceof ConfigurablePackageInterface) {
            $defaults = $package->getDefaultSettings();
            $settings = array_merge($defaults, $settings);
        }

        Cache::put($cacheKey, $settings, self::CACHE_TTL);

        return $settings;
    }

    /**
     * Obtiene un setting específico
     */
    public function get(string $packageName, string $key, mixed $default = null): mixed
    {
        $settings = $this->getPackageSettings($packageName);

        return $settings[$key] ?? $default;
    }

    /**
     * Actualiza los settings de un package
     */
    public function updatePackageSettings(string $packageName, array $settings): bool
    {
        $package = $this->discoveryService->getPackage($packageName);

        if (! $package instanceof ConfigurablePackageInterface) {
            return false;
        }

        // Validar settings
        if (! $this->validateSettings($packageName, $settings)) {
            return false;
        }

        // Obtener schema para tipos y encriptación
        $schema = $package->getSettingsSchema()['schema'] ?? [];

        // Guardar en DB
        $this->repository->setMany($packageName, $settings, $schema);

        // Limpiar caché
        $this->clearCache($packageName);

        // Hook post-update
        $package->onSettingsUpdated($settings);

        return true;
    }

    /**
     * Valida settings según el schema del package
     */
    public function validateSettings(string $packageName, array $settings): bool
    {
        $schema = $this->getPackageSchema($packageName);

        if (! $schema) {
            return false;
        }

        $rules = [];
        foreach ($schema['schema'] ?? [] as $key => $field) {
            if (isset($field['validation'])) {
                $rules[$key] = $field['validation'];
            }
        }

        if (empty($rules)) {
            return true;
        }

        $validator = Validator::make($settings, $rules);

        return ! $validator->fails();
    }

    /**
     * Resetea los settings de un package a sus valores por defecto
     */
    public function resetToDefaults(string $packageName): bool
    {
        $package = $this->discoveryService->getPackage($packageName);

        if (! $package instanceof ConfigurablePackageInterface) {
            return false;
        }

        $defaults = $package->getDefaultSettings();
        $schema = $package->getSettingsSchema()['schema'] ?? [];

        $this->repository->reset($packageName, $defaults);
        $this->clearCache($packageName);

        return true;
    }

    /**
     * Limpia la caché de settings de un package
     */
    public function clearCache(?string $packageName = null): void
    {
        if ($packageName) {
            Cache::forget(self::CACHE_PREFIX.$packageName);
        } else {
            // Limpiar caché de todos los packages
            $packages = $this->getConfigurablePackages();
            foreach ($packages as $package) {
                Cache::forget(self::CACHE_PREFIX.$package->getName());
            }
        }
    }

    /**
     * Obtiene información de todos los packages configurables para el frontend
     */
    public function getSettingsForFrontend(): array
    {
        $packages = $this->getConfigurablePackages();
        $result = [];

        foreach ($packages as $package) {
            $packageName = $package->getName();
            $schema = $package->getSettingsSchema();
            $settings = $this->getPackageSettings($packageName);

            $result[$packageName] = [
                'name' => $packageName,
                'schema' => $schema,
                'settings' => $settings,
                'hasSettings' => $package->hasSettings(),
            ];
        }

        return $result;
    }

    /**
     * Exporta los settings de un package
     */
    public function export(string $packageName): array
    {
        return $this->getPackageSettings($packageName, useCache: false);
    }

    /**
     * Importa settings de un package
     */
    public function import(string $packageName, array $settings): bool
    {
        return $this->updatePackageSettings($packageName, $settings);
    }
}
