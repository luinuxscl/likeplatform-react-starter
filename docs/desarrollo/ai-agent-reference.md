# 🤖 Referencia Rápida para Agentes IA - Laravel 12 React Starter Kit

**Propósito**: Guía de referencia rápida para agentes de programación que crean packages o features.

---

## 📋 Información del Proyecto

### Stack Tecnológico
- **Backend**: Laravel 12 (PHP 8.3.6)
- **Frontend**: React 19 + TypeScript + Inertia v2
- **UI**: Tailwind 4 + shadcn/ui + Radix UI
- **Testing**: Pest v4
- **Formatter**: Laravel Pint v1

### Estructura de Directorios
```
app/
├── Http/Controllers/
├── Models/
├── Traits/              # Traits reutilizables
└── ...

packages/                # Packages personalizados
├── fcv/
├── ejemplo/
└── [tu-package]/

resources/js/
├── components/
│   ├── ui/             # shadcn/ui components
│   ├── expansion/      # Components del sistema
│   └── theme/          # Components de temas
├── hooks/              # React hooks
├── contexts/           # React contexts
├── pages/              # Inertia pages
├── types/              # TypeScript types
└── lib/                # Utilidades

tests/
├── Feature/            # Feature tests
└── Unit/               # Unit tests

docs/
├── guias/              # Guías prácticas
├── sistemas/           # Documentación de sistemas
├── desarrollo/         # Guidelines
└── implementaciones/   # Reportes de implementación
```

---

## 🎨 Sistema de Temas y Personalización

### 1. Configurar Tema Durante Instalación del Package

**Trait disponible**: `App\Traits\ConfiguresTheme`

**Uso en InstallCommand:**

```php
<?php

namespace MiVendor\MiPackage\Console\Commands;

use App\Traits\ConfiguresTheme;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    use ConfiguresTheme;

    protected $signature = 'mi-package:install 
                            {--theme= : Tema a usar}
                            {--no-theme : No cambiar el tema}';

    public function handle(): int
    {
        // ... otras tareas de instalación ...

        // Configurar tema
        if (!$this->option('no-theme')) {
            $suggestedTheme = config('mi-package.theme', 'zinc');
            
            if ($theme = $this->option('theme')) {
                $this->setDefaultTheme($theme, true);
            } elseif ($this->askToChangeTheme($suggestedTheme, 'Mi Package')) {
                $this->setDefaultTheme($suggestedTheme, true);
            }
        }

        return self::SUCCESS;
    }
}
```

**Métodos disponibles del trait:**
- `setDefaultTheme(string $theme, bool $updateEnv = true): bool`
- `registerCustomTheme(string $key, array $config): void`
- `askToChangeTheme(string $suggestedTheme, string $packageName): bool`

**Temas disponibles (12):**
- Neutrales: zinc, slate, stone, gray, neutral
- Colores: red, rose, orange, green, blue, yellow, violet

**Configuración en config/mi-package.php:**
```php
return [
    'theme' => env('MI_PACKAGE_THEME', 'blue'),
    // ...
];
```

**Documentación completa**: `docs/guias/package-theme-installation.md`

---

### 2. Usar Temas en el Frontend

**Hook para temas shadcn/ui:**
```typescript
import { useTheme } from '@/hooks/useShadcnTheme'

function MyComponent() {
  const { currentTheme, availableThemes, setTheme } = useTheme()
  
  // Cambiar tema
  setTheme('blue')
  
  // Listar temas disponibles
  Object.entries(availableThemes).map(([key, config]) => ...)
}
```

**Hook para dark/light mode:**
```typescript
import { useAppearance } from '@/hooks/use-appearance'

function MyComponent() {
  const { appearance, updateAppearance } = useAppearance()
  
  // Cambiar modo
  updateAppearance('dark')  // 'light' | 'dark' | 'system'
}
```

**Documentación completa**: `docs/sistemas/shadcn-themes.md`

---

## 📦 Sistema de Packages

### Estructura de un Package

```
packages/mi-package/
├── config/
│   ├── mi-package.php      # Configuración
│   └── menu.php            # Menú (auto-discovery)
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/                 # Componentes React
│   ├── lang/               # Traducciones
│   └── views/              # Vistas Blade
├── routes/
│   └── mi-package.php      # Rutas
├── src/
│   ├── Console/Commands/
│   ├── Http/Controllers/
│   ├── Models/
│   └── Providers/
│       └── MiPackageServiceProvider.php
└── tests/
```

### ServiceProvider Básico

```php
<?php

namespace MiVendor\MiPackage\Providers;

use Illuminate\Support\ServiceProvider;

class MiPackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/mi-package.php', 'mi-package');
    }

    public function boot(): void
    {
        // Rutas
        $this->loadRoutesFrom(__DIR__.'/../../routes/mi-package.php');
        
        // Migraciones
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        
        // Traducciones
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'mi-package');
        
        // Vistas
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'mi-package');
        
        // Publicar configuración
        $this->publishes([
            __DIR__.'/../../config/mi-package.php' => config_path('mi-package.php'),
        ], 'mi-package-config');
        
        // Publicar componentes React
        $this->publishes([
            __DIR__.'/../../resources/js' => resource_path('js/vendor/mi-package'),
        ], 'mi-package-js');
    }
}
```

**Documentación completa**: `docs/sistemas/packages-personalizacion.md`

---

## 🔐 Sistema de Permisos (Spatie)

### Crear Roles Durante Instalación

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// En InstallCommand o Seeder
$guard = config('auth.defaults.guard', 'web');

// Crear roles
$adminRole = Role::findOrCreate('mi-package-admin', $guard);
$userRole = Role::findOrCreate('mi-package-user', $guard);

// Crear permisos
$permissions = [
    'mi-package.view',
    'mi-package.create',
    'mi-package.edit',
    'mi-package.delete',
];

foreach ($permissions as $permission) {
    Permission::findOrCreate($permission, $guard);
}

// Asignar permisos a roles
$adminRole->givePermissionTo($permissions);
$userRole->givePermissionTo(['mi-package.view']);
```

### Usar en Rutas

```php
Route::middleware(['auth', 'role:mi-package-admin'])->group(function () {
    Route::get('/mi-package/admin', [AdminController::class, 'index']);
});

Route::middleware(['auth', 'permission:mi-package.view'])->group(function () {
    Route::get('/mi-package', [MiPackageController::class, 'index']);
});
```

---

## 🧪 Testing con Pest

### Feature Test Básico

```php
<?php

use App\Models\User;

it('can access mi-package dashboard', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->get('/mi-package');
    
    $response->assertOk();
});

it('requires authentication', function () {
    $response = $this->get('/mi-package');
    
    $response->assertRedirect('/login');
});
```

### Ejecutar Tests

```bash
# Todos los tests
vendor/bin/pest

# Tests específicos
vendor/bin/pest tests/Feature/MiPackageTest.php

# Con cobertura
vendor/bin/pest --coverage
```

---

## 🎯 Sistema de Menús (Auto-Discovery)

### Configurar Menú del Package

**Archivo**: `packages/mi-package/config/menu.php`

```php
<?php

return [
    'items' => [
        [
            'label' => 'Mi Package',
            'icon' => 'Package',  // Lucide icon name
            'route' => 'mi-package.index',
            'permission' => 'mi-package.view',
            'order' => 100,
            'children' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'mi-package.dashboard',
                ],
                [
                    'label' => 'Settings',
                    'route' => 'mi-package.settings',
                    'permission' => 'mi-package.admin',
                ],
            ],
        ],
    ],
];
```

**El sistema carga automáticamente** todos los archivos `config/menu.php` de los packages.

---

## 🌐 Internacionalización (i18n)

### Archivos de Traducción

```
packages/mi-package/resources/lang/
├── en/
│   └── messages.php
└── es/
    └── messages.php
```

**Ejemplo** (`en/messages.php`):
```php
<?php

return [
    'welcome' => 'Welcome to Mi Package',
    'dashboard' => 'Dashboard',
    'settings' => 'Settings',
];
```

### Usar en Backend

```php
__('mi-package::messages.welcome')
trans('mi-package::messages.dashboard')
```

### Usar en Frontend (Inertia)

```typescript
import { usePage } from '@inertiajs/react'

function MyComponent() {
  const { i18n } = usePage().props
  
  return <h1>{i18n.locale === 'es' ? 'Hola' : 'Hello'}</h1>
}
```

---

## 📝 Componentes React/Inertia

### Estructura de Página Inertia

```typescript
import { Head } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'

interface Props {
  data: any[]
}

export default function MiPackagePage({ data }: Props) {
  return (
    <AppLayout>
      <Head title="Mi Package" />
      
      <div className="p-6">
        <h1 className="text-2xl font-bold">Mi Package</h1>
        {/* Contenido */}
      </div>
    </AppLayout>
  )
}
```

### Usar Componentes shadcn/ui

```typescript
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'

function MyComponent() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Título</CardTitle>
      </CardHeader>
      <CardContent>
        <Input placeholder="Escribe algo..." />
        <Button>Guardar</Button>
      </CardContent>
    </Card>
  )
}
```

**Componentes disponibles**: Ver `resources/js/components/ui/`

---

## 🔄 Inertia v2 Features

### Deferred Props

```php
// En Controller
return Inertia::render('MiPackage/Index', [
    'users' => User::all(),
    'stats' => Inertia::defer(fn () => $this->getExpensiveStats()),
]);
```

```typescript
// En React
import { usePage } from '@inertiajs/react'

function MyComponent() {
  const { stats } = usePage().props
  
  // stats se cargará después del render inicial
}
```

### Polling

```typescript
import { router } from '@inertiajs/react'

// Polling cada 5 segundos
useEffect(() => {
  const interval = setInterval(() => {
    router.reload({ only: ['stats'] })
  }, 5000)
  
  return () => clearInterval(interval)
}, [])
```

### Prefetching

```typescript
import { Link } from '@inertiajs/react'

<Link 
  href="/mi-package/details" 
  prefetch
>
  Ver Detalles
</Link>
```

---

## 📊 Convenciones de Código

### PHP (Laravel Pint)

```bash
# Formatear código
vendor/bin/pint

# Verificar sin cambiar
vendor/bin/pint --test
```

### TypeScript/React

- Usar **functional components** con hooks
- **TypeScript** para todos los archivos
- Props interfaces con `interface Props {}`
- Nombres de componentes en **PascalCase**
- Hooks personalizados con prefijo `use`

### Commits

```
feat: Descripción breve

Descripción más detallada si es necesario.

- Cambio 1
- Cambio 2
```

**Tipos**: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`

---

## 🚀 Comandos Útiles

### Laravel

```bash
# Migraciones
php artisan migrate
php artisan migrate:rollback

# Seeders
php artisan db:seed --class=MiPackageSeeder

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpiar cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### NPM

```bash
# Desarrollo
npm run dev

# Producción
npm run build

# Verificar TypeScript
npm run type-check
```

### Testing

```bash
# Todos los tests
vendor/bin/pest

# Con coverage
vendor/bin/pest --coverage

# Tests específicos
vendor/bin/pest --filter=MiPackageTest
```

---

## 📚 Documentación de Referencia

### Sistemas Principales
- **Packages**: `docs/sistemas/packages-personalizacion.md`
- **Temas**: `docs/sistemas/shadcn-themes.md`
- **Settings**: `docs/sistemas/settings-dinamicos.md`
- **Menús**: `docs/sistemas/menus-dinamicos.md`

### Guías Prácticas
- **Crear Package**: `docs/guias/crear-package.md`
- **Temas en Instalación**: `docs/guias/package-theme-installation.md`
- **Testing**: `docs/guias/testing.md`

### Desarrollo
- **Convenciones**: `docs/desarrollo/convenciones-codigo.md`
- **Git Workflow**: `docs/desarrollo/git-workflow.md`
- **TypeScript**: `docs/desarrollo/typescript-guidelines.md`

---

## ✅ Checklist para Crear un Package

- [ ] Crear estructura de directorios
- [ ] ServiceProvider con registro y boot
- [ ] Configuración en `config/mi-package.php`
- [ ] Menú en `config/menu.php` (opcional)
- [ ] Migraciones en `database/migrations/`
- [ ] Rutas en `routes/mi-package.php`
- [ ] Controllers en `src/Http/Controllers/`
- [ ] Models en `src/Models/`
- [ ] Componentes React en `resources/js/`
- [ ] InstallCommand con `ConfiguresTheme` trait
- [ ] Configurar tema sugerido
- [ ] Tests en `tests/Feature/`
- [ ] Documentación en README del package
- [ ] Registrar en `composer.json` (autoload)
- [ ] Registrar en `config/app.php` (providers)

---

## 🔗 Enlaces Rápidos

- **Laravel 12 Docs**: https://laravel.com/docs/12.x
- **React 19 Docs**: https://react.dev
- **Inertia v2 Docs**: https://inertiajs.com
- **shadcn/ui**: https://ui.shadcn.com
- **Tailwind 4**: https://tailwindcss.com
- **Pest v4**: https://pestphp.com

---

**Última actualización**: 2025-10-11  
**Versión del Starter Kit**: 1.0.0  
**Mantenido por**: Equipo de desarrollo

---

## 💡 Tips para Agentes IA

1. **Siempre usar el trait `ConfiguresTheme`** cuando crees un package con identidad visual
2. **Seguir la estructura de packages existentes** (ej: FCV) como referencia
3. **Crear tests** para toda funcionalidad nueva
4. **Documentar** en el README del package
5. **Usar TypeScript** para todo el código frontend
6. **Respetar convenciones** de nombres y estructura
7. **Verificar que los tests pasen** antes de commit
8. **Actualizar CHANGELOG.md** con cambios importantes

---

**Este archivo es la referencia rápida. Para detalles completos, consultar la documentación específica en `docs/`.**
