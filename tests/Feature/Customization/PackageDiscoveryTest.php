<?php

use App\Services\PackageDiscoveryService;
use Illuminate\Support\Facades\Cache;

test('package discovery service can discover packages', function () {
    $service = app(PackageDiscoveryService::class);

    $packages = $service->discover(useCache: false);

    expect($packages)->toBeArray();
});

test('package discovery service uses cache', function () {
    // Primero descubrir sin cache para poblar
    $service = app(PackageDiscoveryService::class);
    $packages = $service->discover(useCache: false);

    // Limpiar y crear nueva instancia
    $service->clearCache();

    // Cachear manualmente
    $packageClasses = array_map(fn ($pkg) => get_class($pkg), $packages);
    Cache::put('customization_packages_discovered', $packageClasses, 3600);

    // Nueva instancia para probar cache
    $service2 = app(PackageDiscoveryService::class);
    $cachedPackages = $service2->discover(useCache: true);

    expect($cachedPackages)->toBeArray();
    expect(count($cachedPackages))->toBe(count($packages));
});

test('package discovery service can clear cache', function () {
    $service = app(PackageDiscoveryService::class);

    $service->clearCache();

    expect(Cache::has('customization_packages_discovered'))->toBeFalse();
});

test('package discovery service returns enabled packages only', function () {
    $service = app(PackageDiscoveryService::class);

    $enabledPackages = $service->getEnabledPackages();

    expect($enabledPackages)->toBeArray();

    foreach ($enabledPackages as $package) {
        expect($package->isEnabled())->toBeTrue();
    }
});
