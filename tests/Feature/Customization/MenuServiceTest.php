<?php

use App\Services\MenuService;
use App\Services\PackageDiscoveryService;

test('menu service can compile menus from packages', function () {
    $service = app(MenuService::class);
    
    $menus = $service->getMenuItems(useCache: false);
    
    expect($menus)->toBeArray()
        ->and($menus)->toHaveKeys(['platform', 'admin', 'operation']);
});

test('menu service filters items by permissions', function () {
    $service = app(MenuService::class);
    
    $items = [
        ['title' => 'Public Item', 'href' => '/public'],
        ['title' => 'Protected Item', 'href' => '/protected', 'permission' => 'test.permission'],
    ];
    
    $filtered = $service->filterByPermissions($items, []);
    
    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]['title'])->toBe('Public Item');
});

test('menu service can clear cache', function () {
    $service = app(MenuService::class);
    
    $service->clearCache();
    
    expect(Cache::has('customization_menus_compiled'))->toBeFalse();
});

test('menu service returns items for specific section', function () {
    $service = app(MenuService::class);
    
    $platformItems = $service->getMenuItemsForSection('platform', filterPermissions: false);
    
    expect($platformItems)->toBeArray();
});
