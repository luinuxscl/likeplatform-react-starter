# Guía Rápida: Crear un Package en 5 Minutos

## 🚀 Inicio Rápido

### 1. Crear Estructura Básica

```bash
mkdir -p packages/mi-empresa/mi-solucion/{src,config,database/migrations,resources/js/pages}
cd packages/mi-empresa/mi-solucion
```

### 2. composer.json

```json
{
    "name": "mi-empresa/mi-solucion",
    "description": "Mi solución personalizada",
    "type": "library",
    "autoload": {
        "psr-4": {
            "MiEmpresa\\MiSolucion\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "MiEmpresa\\MiSolucion\\MiSolucionServiceProvider"
            ]
        }
    }
}
```

### 3. src/Package.php

```php
<?php

namespace MiEmpresa\MiSolucion;

use App\Support\CustomizationPackage;

class Package extends CustomizationPackage
{
    public function getName(): string
    {
        return 'mi-solucion';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }
}
```

### 4. config/menu.php

```php
<?php

return [
    'items' => [
        [
            'section' => 'operation',
            'label' => 'Mi Solución',
            'icon' => 'Sparkles',
            'route' => 'mi-solucion.index',
            'permission' => 'mi-solucion.view',
            'order' => 10,
        ],
    ],
    
    'routes' => [
        [
            'method' => 'GET',
            'uri' => '/mi-solucion',
            'action' => 'MiEmpresa\MiSolucion\Http\Controllers\IndexController@index',
            'middleware' => ['web', 'auth'],
            'name' => 'mi-solucion.index',
        ],
    ],
    
    'permissions' => [
        [
            'name' => 'mi-solucion.view',
            'description' => 'Ver Mi Solución',
        ],
    ],
];
```

### 5. src/MiSolucionServiceProvider.php

```php
<?php

namespace MiEmpresa\MiSolucion;

use Illuminate\Support\ServiceProvider;

class MiSolucionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/menu.php', 'mi-solucion');
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

### 6. src/Http/Controllers/IndexController.php

```php
<?php

namespace MiEmpresa\MiSolucion\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function index()
    {
        return Inertia::render('MiSolucion/Index', [
            'title' => 'Mi Solución',
        ]);
    }
}
```

### 7. resources/js/pages/Index.tsx

```tsx
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function Index({ title }: { title: string }) {
    return (
        <AppLayout>
            <Head title={title} />
            <Card>
                <CardHeader>
                    <CardTitle>{title}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p>¡Mi solución está funcionando!</p>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
```

### 8. src/Console/Commands/InstallCommand.php

```php
<?php

namespace MiEmpresa\MiSolucion\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'mi-solucion:install';
    protected $description = 'Instala Mi Solución';

    public function handle(): int
    {
        $this->info('🚀 Instalando Mi Solución...');
        
        $this->call('migrate', [
            '--path' => 'packages/mi-empresa/mi-solucion/database/migrations',
        ]);
        
        $this->info('✅ Instalación completada!');
        return self::SUCCESS;
    }
}
```

### 9. Instalar el Package

```bash
# En el root del proyecto
composer require mi-empresa/mi-solucion

# Instalar
php artisan mi-solucion:install

# Limpiar caché
php artisan customization:clear-cache
```

### 9.1 Requerir packages locales en paralelo

```bash
# Dentro del proyecto principal (demo1)
composer require mi-empresa/mi-solucion:*

# Composer detecta el package en ../packages/ y crea un symlink en vendor/
ls -l vendor/mi-empresa/mi-solucion

# Opcional: forzar actualización del symlink
composer update mi-empresa/mi-solucion --prefer-source
```

- **Repositorio path**: `composer.json` ya declara `../packages/*` con `"symlink": true`, por lo que no es necesario publicar ni copiar archivos al proyecto base.
- **Hot reload**: al ejecutar `npm run dev`, Vite sigue los symlinks y recompila cada vez que edites `../packages/mi-empresa/mi-solucion/resources/js`.
- **Imports React**: importa componentes con `import Widget from '@mi-solucion/components/widget';` gracias al alias `@mi-solucion` definido en `vite.config.ts`.

### 10. Verificar

```bash
# Listar packages
php artisan customization:list-packages

# Visitar en el navegador
# http://localhost/mi-solucion
```

## ✅ ¡Listo!

Tu package ahora aparece automáticamente en el sidebar bajo "Operación" con el icono ✨ Sparkles.

## 📚 Siguiente Paso

Lee la documentación completa en `docs/SISTEMA_PACKAGES_PERSONALIZACION.md` para features avanzadas.
