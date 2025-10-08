<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Packages de Personalización
    |--------------------------------------------------------------------------
    |
    | Configuración de packages instalados. Cada package puede ser habilitado
    | o deshabilitado individualmente.
    |
    */

    'packages' => [
        // Ejemplo:
        // 'mi-package' => [
        //     'enabled' => true,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery
    |--------------------------------------------------------------------------
    |
    | Habilita o deshabilita el auto-discovery de packages en el directorio
    | packages/. Si está deshabilitado, los packages deben registrarse
    | manualmente en el array 'packages'.
    |
    */

    'auto_discovery' => env('CUSTOMIZATION_AUTO_DISCOVERY', true),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configuración de caché para packages y menús descubiertos.
    |
    */

    'cache' => [
        'enabled' => env('CUSTOMIZATION_CACHE_ENABLED', true),
        'ttl' => env('CUSTOMIZATION_CACHE_TTL', 3600), // 1 hora
    ],

    /*
    |--------------------------------------------------------------------------
    | Secciones de Menú
    |--------------------------------------------------------------------------
    |
    | Define las secciones de menú disponibles y su orden de visualización.
    |
    */

    'menu_sections' => [
        'platform' => [
            'label' => 'Plataforma',
            'order' => 1,
        ],
        'operation' => [
            'label' => 'Operación',
            'order' => 2,
        ],
        'admin' => [
            'label' => 'Administración',
            'order' => 3,
            'requires_role' => 'admin',
        ],
    ],
];
