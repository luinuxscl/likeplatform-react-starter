<?php

namespace App\Repositories;

use App\Models\PackageSetting;
use Illuminate\Support\Collection;

/**
 * Repository para gestionar settings de packages
 */
class SettingsRepository
{
    /**
     * Obtiene todos los settings de un package
     */
    public function getPackageSettings(string $packageName): array
    {
        return PackageSetting::getPackageSettings($packageName);
    }

    /**
     * Obtiene un setting específico
     */
    public function get(string $packageName, string $key, mixed $default = null): mixed
    {
        $setting = PackageSetting::forPackage($packageName)
            ->forKey($key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Guarda o actualiza un setting
     */
    public function set(
        string $packageName,
        string $key,
        mixed $value,
        string $type = 'string',
        bool $encrypted = false
    ): PackageSetting {
        return PackageSetting::updateOrCreate(
            [
                'package_name' => $packageName,
                'key' => $key,
            ],
            [
                'value' => $value,
                'type' => $type,
                'encrypted' => $encrypted,
            ]
        );
    }

    /**
     * Guarda múltiples settings de un package
     */
    public function setMany(string $packageName, array $settings, array $schema = []): void
    {
        foreach ($settings as $key => $value) {
            $fieldSchema = $schema[$key] ?? [];
            $type = $fieldSchema['type'] ?? 'string';
            $encrypted = $fieldSchema['encrypted'] ?? false;

            $this->set($packageName, $key, $value, $type, $encrypted);
        }
    }

    /**
     * Elimina un setting
     */
    public function delete(string $packageName, string $key): bool
    {
        return PackageSetting::forPackage($packageName)
            ->forKey($key)
            ->delete() > 0;
    }

    /**
     * Elimina todos los settings de un package
     */
    public function deletePackageSettings(string $packageName): int
    {
        return PackageSetting::forPackage($packageName)->delete();
    }

    /**
     * Verifica si existe un setting
     */
    public function has(string $packageName, string $key): bool
    {
        return PackageSetting::forPackage($packageName)
            ->forKey($key)
            ->exists();
    }

    /**
     * Obtiene todos los packages que tienen settings
     */
    public function getPackagesWithSettings(): Collection
    {
        return PackageSetting::select('package_name')
            ->distinct()
            ->pluck('package_name');
    }

    /**
     * Cuenta los settings de un package
     */
    public function count(string $packageName): int
    {
        return PackageSetting::forPackage($packageName)->count();
    }

    /**
     * Resetea los settings de un package a sus valores por defecto
     */
    public function reset(string $packageName, array $defaults): void
    {
        $this->deletePackageSettings($packageName);
        $this->setMany($packageName, $defaults);
    }
}
