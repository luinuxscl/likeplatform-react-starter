# Sistema de Packages de Personalización con Auto-Discovery

## 📋 Índice

1. [Introducción](#introducción)
2. [Arquitectura](#arquitectura)
3. [Instalación](#instalación)
4. [Crear un Package](#crear-un-package)
5. [Configuración de Menús](#configuración-de-menús)
6. [Registro de Rutas](#registro-de-rutas)
7. [Gestión de Permisos](#gestión-de-permisos)
8. [Componentes React](#componentes-react)
9. [Comandos Artisan](#comandos-artisan)
10. [Caché y Performance](#caché-y-performance)
11. [Troubleshooting](#troubleshooting)

---

## Introducción

El sistema de packages de personalización permite extender el Laravel 12 + React 19 Starter Kit mediante módulos independientes que:

- ✅ Se instalan vía Composer
- ✅ Definen sus propios items de menú mediante configuración
- ✅ Se auto-descubren e integran automáticamente en el sidebar
- ✅ No requieren modificar el código base del starterkit
- ✅ Soportan iconos dinámicos de Lucide
- ✅ Gestionan permisos con Spatie Laravel Permission
- ✅ Incluyen componentes React integrados

---

## Arquitectura

### Componentes del Sistema

```
app/
├── Contracts/
│   └── CustomizationPackageInterface.php    # Interface estándar
├── Support/
│   └── CustomizationPackage.php             # Clase base abstracta
├── Services/
│   ├── PackageDiscoveryService.php          # Auto-discovery
│   └── MenuService.php                      # Gestión de menús
├── Providers/
│   └── CustomizationServiceProvider.php     # ServiceProvider principal
└── Console/Commands/
    ├── CustomizationClearCacheCommand.php   # Limpiar caché
    └── CustomizationListPackagesCommand.php # Listar packages

config/
└── customization.php                        # Configuración del sistema

resources/js/
├── lib/
│   └── icon-resolver.ts                     # Resolver iconos dinámicos
└── types/
    └── index.d.ts                           # TypeScript types
```

### Flujo de Funcionamiento

1. **Discovery**: `PackageDiscoveryService` escanea `packages/` buscando packages válidos
2. **Registro**: `CustomizationServiceProvider` registra rutas y servicios
3. **Compilación**: `MenuService` compila menús de todos los packages habilitados
4. **Compartir**: Menús se comparten con Inertia vía props
5. **Renderizado**: `app-sidebar.tsx` renderiza menús dinámicamente con iconos de Lucide

---

## Instalación

El sistema ya está instalado en el starterkit. Para verificar:

```bash
php artisan customization:list-packages
```

---

## Crear un Package

### 1. Estructura del Package

```
packages/
└── vendor-name/
    └── package-name/
        ├── composer.json
        ├── README.md
        ├── config/
        │   └── menu.php
        ├── database/
        │   ├── migrations/
        │   └── seeders/
        ├── resources/
        │   └── js/
        │       └── pages/
        ├── src/
        │   ├── Http/
        │   │   └── Controllers/
        │   ├── Models/
        │   ├── Console/
        │   │   └── Commands/
        │   ├── Package.php
        │   └── PackageServiceProvider.php
        └── tests/
```

### 2. composer.json

```json
{
    "name": "vendor/package-name",
    "description": "Descripción del package",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "laravel/framework": "^12.0"
    },
    "autoload": {
        "psr-4": {
            "Vendor\\PackageName\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Vendor\\PackageName\\PackageServiceProvider"
            ]
        }
    }
}
```

### 3. Clase Package.php

```php
<?php

namespace Vendor\PackageName;

use App\Support\CustomizationPackage;

class Package extends CustomizationPackage
{
    public function getName(): string
    {
        return 'package-name';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function install(): void
    {
        // Lógica de instalación
        \Artisan::call('migrate', [
            '--path' => 'packages/vendor/package-name/database/migrations',
            '--force' => true,
        ]);
    }
}
```

### 4. ServiceProvider

```php
<?php

namespace Vendor\PackageName;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/menu.php',
            'package-name'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\InstallCommand::class,
            ]);
        }
    }
}
```

---

## Configuración de Menús

### config/menu.php

```php
<?php

return [
    'items' => [
        [
            'section' => 'operation',      // 'platform', 'operation', 'admin'
            'label' => 'Mi Módulo',        // Texto del menú
            'icon' => 'Package',           // Nombre del icono de Lucide
            'route' => 'mi-modulo.index',  // Ruta nombrada
            'permission' => 'mi-modulo.view', // Permiso requerido (opcional)
            'order' => 10,                 // Orden de visualización
            'active' => true,              // Habilitado/Deshabilitado
        ],
    ],
];
```

### Secciones Disponibles

- **platform**: Sección principal de la plataforma
- **operation**: Sección de operaciones (recomendado para packages)
- **admin**: Sección de administración (requiere rol admin)

### Iconos Disponibles

Todos los iconos de [Lucide](https://lucide.dev/icons/) están disponibles. Ejemplos:

- `Package`, `Users`, `Settings`, `Database`
- `FileText`, `Calendar`, `Mail`, `Bell`
- `ShoppingCart`, `CreditCard`, `TrendingUp`

---

## Registro de Rutas

### En config/menu.php

```php
'routes' => [
    [
        'method' => 'GET',
        'uri' => '/mi-modulo',
        'action' => 'Vendor\PackageName\Http\Controllers\Controller@index',
        'middleware' => ['web', 'auth'],
        'name' => 'mi-modulo.index',
    ],
    [
        'method' => 'POST',
        'uri' => '/mi-modulo/store',
        'action' => 'Vendor\PackageName\Http\Controllers\Controller@store',
        'middleware' => ['web', 'auth', 'can:mi-modulo.create'],
        'name' => 'mi-modulo.store',
    ],
],
```

### Métodos HTTP Soportados

- `GET`, `POST`, `PUT`, `PATCH`, `DELETE`

### Middleware Recomendados

- `web`: Sesiones, CSRF, cookies
- `auth`: Requiere autenticación
- `can:permission`: Requiere permiso específico

---

## Gestión de Permisos

### Definir Permisos en config/menu.php

```php
'permissions' => [
    [
        'name' => 'mi-modulo.view',
        'description' => 'Ver el módulo',
        'guard_name' => 'web',
    ],
    [
        'name' => 'mi-modulo.create',
        'description' => 'Crear items',
        'guard_name' => 'web',
    ],
],
```

### Seeder de Permisos

```php
<?php

namespace Vendor\PackageName\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'mi-modulo.view' => 'Ver el módulo',
            'mi-modulo.create' => 'Crear items',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Asignar al rol admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }
    }
}
```

---

## Componentes React

### Estructura de Páginas

```tsx
// resources/js/pages/Index.tsx
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
    title: string;
}

export default function Index({ title }: Props) {
    return (
        <AppLayout>
            <Head title={title} />
            
            <div className="space-y-6">
                <h1 className="text-3xl font-bold">{title}</h1>
                
                <Card>
                    <CardHeader>
                        <CardTitle>Mi Módulo</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p>Contenido del módulo</p>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
```

### Controlador Inertia

```php
<?php

namespace Vendor\PackageName\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class MiModuloController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('MiModulo/Index', [
            'title' => 'Mi Módulo',
        ]);
    }
}
```

---

## Comandos Artisan

### Comando de Instalación

```php
<?php

namespace Vendor\PackageName\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'package-name:install';
    protected $description = 'Instala el package';

    public function handle(): int
    {
        $this->info('🚀 Instalando package...');
        
        // Publicar configuración
        $this->call('vendor:publish', [
            '--tag' => 'package-config',
        ]);
        
        // Ejecutar migraciones
        $this->call('migrate', [
            '--path' => 'packages/vendor/package-name/database/migrations',
        ]);
        
        // Seeders
        $this->call('db:seed', [
            '--class' => 'Vendor\\PackageName\\Database\\Seeders\\PermissionsSeeder',
        ]);
        
        $this->info('✅ Package instalado!');
        
        return self::SUCCESS;
    }
}
```

### Comandos del Sistema

```bash
# Listar packages descubiertos
php artisan customization:list-packages

# Limpiar caché de packages y menús
php artisan customization:clear-cache
```

---

## Caché y Performance

### Sistema de Caché

El sistema cachea automáticamente:

- **Packages descubiertos**: 1 hora
- **Menús compilados**: 1 hora

### Limpiar Caché

```bash
# Limpiar caché del sistema de personalización
php artisan customization:clear-cache

# Limpiar toda la caché de Laravel
php artisan cache:clear
php artisan config:clear
```

### Configuración de Caché

En `config/customization.php`:

```php
'cache' => [
    'enabled' => env('CUSTOMIZATION_CACHE_ENABLED', true),
    'ttl' => env('CUSTOMIZATION_CACHE_TTL', 3600),
],
```

### Variables de Entorno

```env
CUSTOMIZATION_AUTO_DISCOVERY=true
CUSTOMIZATION_CACHE_ENABLED=true
CUSTOMIZATION_CACHE_TTL=3600
```

---

## Troubleshooting

### El menú no aparece

1. Verificar que el package está descubierto:
   ```bash
   php artisan customization:list-packages
   ```

2. Limpiar caché:
   ```bash
   php artisan customization:clear-cache
   ```

3. Verificar permisos del usuario:
   ```bash
   php artisan tinker
   >>> auth()->user()->getAllPermissions()->pluck('name')
   ```

### Iconos no se muestran

- Verificar que el nombre del icono existe en Lucide: https://lucide.dev/icons/
- El nombre debe coincidir exactamente (case-sensitive): `Package`, no `package`

### Rutas no funcionan

1. Verificar que las rutas están registradas:
   ```bash
   php artisan route:list | grep mi-modulo
   ```

2. Limpiar caché de rutas:
   ```bash
   php artisan route:clear
   ```

### Package no se descubre

1. Verificar estructura de directorios: `packages/vendor/package-name/`
2. Verificar que existe `src/Package.php`
3. Verificar que existe `composer.json` con autoload PSR-4
4. Verificar que la clase implementa `CustomizationPackageInterface`

### Errores de permisos

1. Ejecutar seeder de permisos:
   ```bash
   php artisan db:seed --class="Vendor\\PackageName\\Database\\Seeders\\PermissionsSeeder"
   ```

2. Asignar permisos al usuario:
   ```bash
   php artisan tinker
   >>> $user = User::find(1);
   >>> $user->givePermissionTo('mi-modulo.view');
   ```

---

## Ejemplo Completo

Ver el package de ejemplo en: `packages/ejemplo/mi-modulo/`

Para instalarlo:

```bash
# 1. Agregar al composer.json del proyecto
composer require ejemplo/mi-modulo

# 2. Instalar el package
php artisan mi-modulo:install

# 3. Limpiar caché
php artisan customization:clear-cache

# 4. Visitar /mi-modulo
```

---

## Recursos Adicionales

- **Laravel 12 Docs**: https://laravel.com/docs/12.x
- **Inertia.js v2**: https://inertiajs.com/
- **React 19**: https://react.dev/
- **Lucide Icons**: https://lucide.dev/icons/
- **Spatie Permission**: https://spatie.be/docs/laravel-permission/
- **shadcn/ui**: https://ui.shadcn.com/

---

## Soporte

Para reportar bugs o solicitar features, crear un issue en el repositorio del starterkit.
