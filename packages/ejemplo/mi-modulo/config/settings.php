<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mi Módulo Settings Schema
    |--------------------------------------------------------------------------
    |
    | Configuración del package de ejemplo
    |
    */

    'schema' => [
        // General
        'app_name' => [
            'type' => 'text',
            'label' => 'Nombre de la Aplicación',
            'description' => 'Nombre que aparece en el módulo',
            'default' => 'Mi Módulo',
            'validation' => 'required|string|max:100',
            'section' => 'general',
            'placeholder' => 'Ej: Sistema de Gestión',
        ],

        'module_enabled' => [
            'type' => 'boolean',
            'label' => 'Módulo Habilitado',
            'description' => 'Activa o desactiva el módulo completo',
            'default' => true,
            'section' => 'general',
        ],

        'items_per_page' => [
            'type' => 'number',
            'label' => 'Items por Página',
            'description' => 'Cantidad de items a mostrar en el listado',
            'default' => 15,
            'validation' => 'required|integer|min:5|max:100',
            'section' => 'general',
        ],

        'language' => [
            'type' => 'select',
            'label' => 'Idioma',
            'description' => 'Idioma del módulo',
            'default' => 'es',
            'options' => [
                'es' => 'Español',
                'en' => 'English',
                'pt' => 'Português',
            ],
            'section' => 'general',
        ],

        // Características
        'enable_notifications' => [
            'type' => 'boolean',
            'label' => 'Habilitar Notificaciones',
            'description' => 'Enviar notificaciones por email',
            'default' => true,
            'section' => 'features',
        ],

        'enable_export' => [
            'type' => 'boolean',
            'label' => 'Habilitar Exportación',
            'description' => 'Permitir exportar datos a Excel/CSV',
            'default' => true,
            'section' => 'features',
        ],

        'enable_import' => [
            'type' => 'boolean',
            'label' => 'Habilitar Importación',
            'description' => 'Permitir importar datos desde Excel/CSV',
            'default' => false,
            'section' => 'features',
        ],

        // Apariencia
        'primary_color' => [
            'type' => 'color',
            'label' => 'Color Principal',
            'description' => 'Color principal del módulo',
            'default' => '#10b981',
            'section' => 'appearance',
        ],

        'secondary_color' => [
            'type' => 'color',
            'label' => 'Color Secundario',
            'description' => 'Color secundario del módulo',
            'default' => '#fb923c',
            'section' => 'appearance',
        ],

        // Integración
        'api_key' => [
            'type' => 'text',
            'label' => 'API Key',
            'description' => 'Clave de API para integraciones externas',
            'default' => '',
            'validation' => 'nullable|string',
            'section' => 'integration',
            'encrypted' => true,
            'placeholder' => 'sk_live_...',
            'help' => 'Esta clave se almacena encriptada en la base de datos',
        ],

        'api_endpoint' => [
            'type' => 'text',
            'label' => 'API Endpoint',
            'description' => 'URL del endpoint de la API',
            'default' => 'https://api.example.com',
            'validation' => 'nullable|url',
            'section' => 'integration',
            'placeholder' => 'https://api.example.com',
        ],

        'webhook_url' => [
            'type' => 'text',
            'label' => 'Webhook URL',
            'description' => 'URL para recibir webhooks',
            'default' => '',
            'validation' => 'nullable|url',
            'section' => 'integration',
            'placeholder' => 'https://myapp.com/webhooks',
        ],

        // Avanzado
        'debug_mode' => [
            'type' => 'boolean',
            'label' => 'Modo Debug',
            'description' => 'Habilitar logs detallados (solo desarrollo)',
            'default' => false,
            'section' => 'advanced',
        ],

        'cache_enabled' => [
            'type' => 'boolean',
            'label' => 'Caché Habilitado',
            'description' => 'Usar caché para mejorar performance',
            'default' => true,
            'section' => 'advanced',
        ],

        'cache_ttl' => [
            'type' => 'number',
            'label' => 'TTL de Caché (minutos)',
            'description' => 'Tiempo de vida del caché en minutos',
            'default' => 60,
            'validation' => 'required|integer|min:1|max:1440',
            'section' => 'advanced',
        ],
    ],

    'sections' => [
        'general' => 'General',
        'features' => 'Características',
        'appearance' => 'Apariencia',
        'integration' => 'Integración',
        'advanced' => 'Avanzado',
    ],

    'permissions' => [
        'view' => 'mi-modulo.settings.view',
        'edit' => 'mi-modulo.settings.edit',
    ],
];
