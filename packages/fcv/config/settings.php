<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FCV Package Settings Schema
    |--------------------------------------------------------------------------
    |
    | Configuración del package FCV (Control de Acceso)
    |
    */

    'schema' => [
        // General
        'module_name' => [
            'type' => 'text',
            'label' => 'Nombre del Módulo',
            'description' => 'Nombre que aparece en el sidebar',
            'default' => 'FCV Control de Acceso',
            'validation' => 'required|string|max:100',
            'section' => 'general',
        ],

        'enable_guard' => [
            'type' => 'boolean',
            'label' => 'Habilitar Portería',
            'description' => 'Activa o desactiva el módulo de portería',
            'default' => true,
            'section' => 'general',
        ],

        'enable_courses' => [
            'type' => 'boolean',
            'label' => 'Habilitar Cursos',
            'description' => 'Activa o desactiva el módulo de cursos',
            'default' => true,
            'section' => 'general',
        ],

        'enable_organizations' => [
            'type' => 'boolean',
            'label' => 'Habilitar Organizaciones',
            'description' => 'Activa o desactiva el módulo de organizaciones',
            'default' => true,
            'section' => 'general',
        ],

        // Portería
        'guard_auto_approve' => [
            'type' => 'boolean',
            'label' => 'Auto-aprobar Accesos',
            'description' => 'Aprobar automáticamente los accesos de personas registradas',
            'default' => false,
            'section' => 'guard',
        ],

        'guard_notification_email' => [
            'type' => 'text',
            'label' => 'Email de Notificaciones',
            'description' => 'Email para recibir notificaciones de accesos',
            'default' => '',
            'validation' => 'nullable|email',
            'section' => 'guard',
            'placeholder' => 'porteria@fcv.org',
        ],

        'guard_max_daily_entries' => [
            'type' => 'number',
            'label' => 'Máximo de Entradas Diarias',
            'description' => 'Número máximo de entradas por persona por día',
            'default' => 5,
            'validation' => 'required|integer|min:1|max:50',
            'section' => 'guard',
        ],

        // Cursos
        'courses_per_page' => [
            'type' => 'number',
            'label' => 'Cursos por Página',
            'description' => 'Cantidad de cursos a mostrar por página',
            'default' => 15,
            'validation' => 'required|integer|min:5|max:100',
            'section' => 'courses',
        ],

        'course_status' => [
            'type' => 'select',
            'label' => 'Estado por Defecto',
            'description' => 'Estado inicial de los cursos nuevos',
            'default' => 'draft',
            'options' => [
                'draft' => 'Borrador',
                'active' => 'Activo',
                'completed' => 'Completado',
                'archived' => 'Archivado',
            ],
            'section' => 'courses',
        ],

        // Organizaciones
        'org_require_approval' => [
            'type' => 'boolean',
            'label' => 'Requerir Aprobación',
            'description' => 'Las nuevas organizaciones requieren aprobación',
            'default' => true,
            'section' => 'organizations',
        ],

        'org_primary_color' => [
            'type' => 'color',
            'label' => 'Color Principal',
            'description' => 'Color principal para organizaciones',
            'default' => '#ef4444',
            'section' => 'organizations',
        ],
    ],

    'sections' => [
        'general' => 'General',
        'guard' => 'Portería',
        'courses' => 'Cursos',
        'organizations' => 'Organizaciones',
    ],

    'permissions' => [
        'view' => 'fcv.settings.view',
        'edit' => 'fcv.settings.edit',
    ],
];
