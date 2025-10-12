<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Widget System Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración del sistema de widgets para el dashboard.
    | Los packages pueden registrar sus propios widgets mediante
    | config/widgets.php en su directorio.
    |
    */

    /**
     * Widgets del core de la aplicación
     */
    'widgets' => [
        [
            'key' => 'welcome',
            'component' => 'WelcomeWidget',
            'title' => 'Welcome',
            'description' => 'Welcome message and quick actions',
            'size' => 'col-span-12',
            'order' => 1,
            'active' => true,
        ],
    ],

    /**
     * Configuración de caché
     */
    'cache' => [
        'enabled' => env('WIDGETS_CACHE_ENABLED', true),
        'ttl' => env('WIDGETS_CACHE_TTL', 3600), // 1 hora
        'prefix' => 'widgets',
    ],

    /**
     * Auto-discovery de widgets desde packages
     */
    'auto_discovery' => [
        'enabled' => env('WIDGETS_AUTO_DISCOVERY', true),
        'paths' => [
            'packages/*/config/widgets.php',
        ],
    ],

    /**
     * Configuración de grid
     */
    'grid' => [
        'columns' => 12,
        'gap' => 6, // Tailwind spacing
        'breakpoints' => [
            'sm' => 640,
            'md' => 768,
            'lg' => 1024,
            'xl' => 1280,
            '2xl' => 1536,
        ],
    ],

    /**
     * Tamaños de widgets predefinidos
     */
    'sizes' => [
        'small' => 'col-span-12 md:col-span-6 lg:col-span-4',
        'medium' => 'col-span-12 md:col-span-6',
        'large' => 'col-span-12',
        'custom' => '', // Permite tamaños personalizados
    ],

    /**
     * Configuración de refresh
     */
    'refresh' => [
        'default_interval' => 300, // 5 minutos
        'min_interval' => 60, // 1 minuto
        'max_interval' => 3600, // 1 hora
    ],

    /**
     * Permisos del sistema de widgets
     */
    'permissions' => [
        'view' => 'widgets.view',
        'customize' => 'widgets.customize',
        'manage' => 'widgets.manage',
    ],
];
