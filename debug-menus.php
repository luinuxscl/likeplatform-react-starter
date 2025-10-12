#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          DIAGNÓSTICO DE MENÚS - PACKAGE FCV                   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// 1. Verificar archivos
echo "1️⃣  VERIFICACIÓN DE ARCHIVOS\n";
echo str_repeat("─", 60) . "\n";

$checks = [
    'Package FCV' => base_path('packages/fcv'),
    'Package.php' => base_path('packages/fcv/src/Package.php'),
    'menu.php' => base_path('packages/fcv/config/menu.php'),
    'composer.json' => base_path('packages/fcv/composer.json'),
];

foreach ($checks as $name => $path) {
    $exists = file_exists($path);
    echo sprintf("   %-20s %s\n", $name . ':', $exists ? '✅ Existe' : '❌ No existe');
}
echo "\n";

// 2. Verificar clase
echo "2️⃣  VERIFICACIÓN DE CLASE\n";
echo str_repeat("─", 60) . "\n";
$classExists = class_exists('Like\\Fcv\\Package');
echo "   Clase Like\\Fcv\\Package: " . ($classExists ? '✅ Existe' : '❌ No existe') . "\n";

if ($classExists) {
    $package = new Like\Fcv\Package(base_path('packages/fcv'));
    echo "   Nombre: " . $package->getName() . "\n";
    echo "   Versión: " . $package->getVersion() . "\n";
    echo "   Habilitado: " . ($package->isEnabled() ? '✅ Sí' : '❌ No') . "\n";
}
echo "\n";

// 3. Package Discovery
echo "3️⃣  PACKAGE DISCOVERY SERVICE\n";
echo str_repeat("─", 60) . "\n";

\Illuminate\Support\Facades\Cache::forget('customization_packages_discovered');
$discoveryService = app(App\Services\PackageDiscoveryService::class);
$packages = $discoveryService->discover(false);

echo "   Packages descubiertos: " . count($packages) . "\n\n";

foreach ($packages as $name => $pkg) {
    echo "   📦 $name\n";
    echo "      Clase: " . get_class($pkg) . "\n";
    echo "      Versión: " . $pkg->getVersion() . "\n";
    echo "      Habilitado: " . ($pkg->isEnabled() ? '✅' : '❌') . "\n";
    echo "      Menús: " . count($pkg->getMenuItems()) . "\n";
    echo "\n";
}

// 4. Menu Service
echo "4️⃣  MENU SERVICE\n";
echo str_repeat("─", 60) . "\n";

\Illuminate\Support\Facades\Cache::forget('customization_menus_compiled');
$menuService = app(App\Services\MenuService::class);
$menus = $menuService->getMenuItems(false);

echo "   Platform: " . count($menus['platform']) . " items\n";
echo "   Admin: " . count($menus['admin']) . " items\n";
echo "   Operation: " . count($menus['operation']) . " items\n\n";

if (count($menus['operation']) > 0) {
    echo "   📋 Menús de Operación:\n";
    foreach ($menus['operation'] as $item) {
        $icon = $item['icon'] ?? 'Sin icono';
        echo "      • {$item['title']} ({$icon}) - Order: {$item['order']}\n";
        echo "        URL: {$item['href']}\n";
    }
    echo "\n";
}

// 5. Inertia Share
echo "5️⃣  INERTIA SHARE (Simulación)\n";
echo str_repeat("─", 60) . "\n";

$inertiaData = [
    'packages' => [
        'menus' => $menus
    ]
];

echo "   Estructura compartida con Inertia:\n";
echo "   {\n";
echo "     packages: {\n";
echo "       menus: {\n";
echo "         platform: [" . count($menus['platform']) . " items],\n";
echo "         admin: [" . count($menus['admin']) . " items],\n";
echo "         operation: [" . count($menus['operation']) . " items]\n";
echo "       }\n";
echo "     }\n";
echo "   }\n\n";

// 6. Recomendaciones
echo "6️⃣  RECOMENDACIONES\n";
echo str_repeat("─", 60) . "\n";

if (count($menus['operation']) > 0) {
    echo "   ✅ Los menús están configurados correctamente en el backend\n\n";
    echo "   Para ver los menús en el frontend:\n";
    echo "   1. Ejecutar: php artisan optimize:clear\n";
    echo "   2. Ejecutar: npm run build (o npm run dev)\n";
    echo "   3. Hard refresh en el navegador (Ctrl+Shift+R)\n";
    echo "   4. O abrir en ventana de incógnito\n";
} else {
    echo "   ❌ No se encontraron menús de operación\n";
    echo "   Verificar packages/fcv/config/menu.php\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    FIN DEL DIAGNÓSTICO                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";
