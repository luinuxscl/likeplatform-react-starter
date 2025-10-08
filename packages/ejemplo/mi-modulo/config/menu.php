<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Items de Menú
    |--------------------------------------------------------------------------
    |
    | Define los items que aparecerán en el sidebar de la aplicación.
    | Secciones disponibles: 'platform', 'operation', 'admin'
    |
    */

    'items' => [
        [
            'section' => 'operation',
            'label' => 'Mi Módulo',
            'icon' => 'Package', // Nombre del icono de Lucide
            'route' => 'mi-modulo.index',
            'permission' => 'mi-modulo.view',
            'order' => 10,
            'active' => true,
        ],
        [
            'section' => 'operation',
            'label' => 'Gestión de Items',
            'icon' => 'ListChecks',
            'route' => 'mi-modulo.items.index',
            'permission' => 'mi-modulo.items.view',
            'order' => 20,
            'active' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rutas del Package
    |--------------------------------------------------------------------------
    |
    | Define las rutas web que debe registrar el package.
    |
    */

    'routes' => [
        [
            'method' => 'GET',
            'uri' => '/mi-modulo',
            'action' => 'Ejemplo\MiModulo\Http\Controllers\MiModuloController@index',
            'middleware' => ['web', 'auth'],
            'name' => 'mi-modulo.index',
        ],
        [
            'method' => 'GET',
            'uri' => '/mi-modulo/items',
            'action' => 'Ejemplo\MiModulo\Http\Controllers\ItemsController@index',
            'middleware' => ['web', 'auth', 'can:mi-modulo.items.view'],
            'name' => 'mi-modulo.items.index',
        ],
        [
            'method' => 'POST',
            'uri' => '/mi-modulo/items',
            'action' => 'Ejemplo\MiModulo\Http\Controllers\ItemsController@store',
            'middleware' => ['web', 'auth', 'can:mi-modulo.items.create'],
            'name' => 'mi-modulo.items.store',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permisos del Package
    |--------------------------------------------------------------------------
    |
    | Define los permisos que debe registrar el package.
    | Estos se crearán automáticamente durante la instalación.
    |
    */

    'permissions' => [
        [
            'name' => 'mi-modulo.view',
            'description' => 'Ver el módulo principal',
            'guard_name' => 'web',
        ],
        [
            'name' => 'mi-modulo.items.view',
            'description' => 'Ver items del módulo',
            'guard_name' => 'web',
        ],
        [
            'name' => 'mi-modulo.items.create',
            'description' => 'Crear items del módulo',
            'guard_name' => 'web',
        ],
        [
            'name' => 'mi-modulo.items.edit',
            'description' => 'Editar items del módulo',
            'guard_name' => 'web',
        ],
        [
            'name' => 'mi-modulo.items.delete',
            'description' => 'Eliminar items del módulo',
            'guard_name' => 'web',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Componentes React
    |--------------------------------------------------------------------------
    |
    | Define los componentes React que deben registrarse en Vite.
    | [alias => path relativo desde el directorio del package]
    |
    */

    'react_components' => [
        '@mi-modulo/Index' => 'resources/js/pages/Index.tsx',
        '@mi-modulo/Items/Index' => 'resources/js/pages/Items/Index.tsx',
    ],
];
