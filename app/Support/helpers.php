<?php

use App\Services\SettingsService;

if (! function_exists('package_setting')) {
    /**
     * Obtiene un setting de un package
     *
     * @param  string  $key  Format: 'package.key' or just 'key' if package is provided
     * @param  mixed  $default
     * @param  string|null  $package
     */
    function package_setting(string $key, mixed $default = null, ?string $package = null): mixed
    {
        $settingsService = app(SettingsService::class);

        // Si el key contiene un punto, separar package y key
        if (str_contains($key, '.') && $package === null) {
            [$package, $key] = explode('.', $key, 2);
        }

        if ($package === null) {
            throw new InvalidArgumentException('Package name is required');
        }

        return $settingsService->get($package, $key, $default);
    }
}

if (! function_exists('settings')) {
    /**
     * Obtiene todos los settings de un package
     *
     * @param  string  $package
     */
    function settings(string $package): array
    {
        $settingsService = app(SettingsService::class);

        return $settingsService->getPackageSettings($package);
    }
}
