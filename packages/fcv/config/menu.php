<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Items de Menú del Package FCV
    |--------------------------------------------------------------------------
    |
    | Define los items que aparecerán en el sidebar de la aplicación.
    |
    */

    'items' => [
        [
            'section' => 'operation',
            'label' => 'Portería',
            'icon' => 'ShieldCheck',  // Lucide icon name
            'route' => '/fcv/guard',
            'permission' => null,  // Sin permiso requerido por ahora
            'order' => 10,
            'active' => true,
        ],
        [
            'section' => 'operation',
            'label' => 'Cursos',
            'icon' => 'BookOpen',  // Lucide icon name
            'route' => '/fcv/courses',
            'permission' => null,
            'order' => 20,
            'active' => true,
        ],
        [
            'section' => 'operation',
            'label' => 'Organizaciones',
            'icon' => 'Building2',  // Lucide icon name
            'route' => '/fcv/organizations',
            'permission' => null,
            'order' => 30,
            'active' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rutas del Package
    |--------------------------------------------------------------------------
    |
    | Las rutas ya están registradas en routes/fcv.php
    | Este array está vacío porque se manejan de forma tradicional
    |
    */

    'routes' => [
        // Las rutas se cargan desde routes/fcv.php en el ServiceProvider
    ],

    /*
    |--------------------------------------------------------------------------
    | Permisos del Package
    |--------------------------------------------------------------------------
    |
    | Define los permisos que debe registrar el package.
    |
    */

    'permissions' => [
        // Los permisos se pueden agregar aquí cuando se implementen
    ],

    /*
    |--------------------------------------------------------------------------
    | Componentes React
    |--------------------------------------------------------------------------
    |
    | Los componentes React ya están en resources/js/
    |
    */

    'react_components' => [
        // Los componentes se manejan de forma tradicional
    ],
];
