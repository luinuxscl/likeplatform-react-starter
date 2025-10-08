<?php

use App\Services\PackageDiscoveryService;
use Illuminate\Support\Facades\Cache;

test('package discovery service can discover packages', function () {
    $service = app(PackageDiscoveryService::class);
    
    $packages = $service->discover(useCache: false);
    
    expect($packages)->toBeArray();
});

test('package discovery service uses cache', function () {
    Cache::shouldReceive('get')
        ->once()
        ->andReturn([]);
    
    $service = app(PackageDiscoveryService::class);
    $service->discover(useCache: true);
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
