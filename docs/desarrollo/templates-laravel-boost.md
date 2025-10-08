# Templates y Helpers para Desarrollo con Laravel-Boost

## Scripts de Desarrollo Automatizado

### 1. Script de Setup Inicial del Paquete

```bash
#!/bin/bash
# scripts/setup-development.sh

echo "🚀 Configurando entorno de desarrollo del paquete expansión..."

# Verificar información de la aplicación base
echo "📊 Analizando aplicación Laravel base..."
php artisan boost:mcp application-info

# Verificar rutas existentes para evitar conflictos
echo "🛣️ Verificando rutas existentes..."
php artisan boost:mcp list-routes --except-vendor=true

# Verificar esquema de base de datos actual
echo "🗄️ Analizando esquema de base de datos..."
php artisan boost:mcp database-schema

# Verificar configuraciones actuales
echo "⚙️ Verificando configuraciones..."
php artisan boost:mcp get-config app.name
php artisan boost:mcp get-config database.default
php artisan boost:mcp get-config inertia

echo "✅ Análisis completado. Revisa los logs para planificar la integración."
```

### 2. Script de Desarrollo Continuo

```bash
#!/bin/bash
# scripts/dev-workflow.sh

echo "🔧 Iniciando workflow de desarrollo..."

# Función para verificar errores
check_errors() {
    echo "🔍 Verificando errores recientes..."
    php artisan boost:mcp last-error
    php artisan boost:mcp browser-logs 10
}

# Función para verificar logs de aplicación
check_logs() {
    echo "📋 Verificando logs de aplicación..."
    php artisan boost:mcp read-log-entries 20
}

# Función para probar código en tinker
test_code() {
    local code="$1"
    echo "🧪 Probando código: $code"
    php artisan boost:mcp tinker --code="$code"
}

# Función para verificar rutas después de cambios
verify_routes() {
    echo "🛣️ Verificando rutas actualizadas..."
    php artisan boost:mcp list-routes --path="expansion"
}

# Función para verificar base de datos después de migraciones
verify_database() {
    echo "🗄️ Verificando estructura de base de datos..."
    php artisan boost:mcp database-schema --filter="expansion"
}

# Menú interactivo
while true; do
    echo ""
    echo "🔧 Laravel-Boost Development Helper"
    echo "1) Verificar errores"
    echo "2) Ver logs"
    echo "3) Probar código"
    echo "4) Verificar rutas"
    echo "5) Verificar base de datos"
    echo "6) Análisis completo"
    echo "0) Salir"
    echo ""
    read -p "Selecciona una opción: " choice

    case $choice in
        1) check_errors ;;
        2) check_logs ;;
        3) 
            read -p "Código a probar: " code
            test_code "$code"
            ;;
        4) verify_routes ;;
        5) verify_database ;;
        6) 
            check_errors
            check_logs
            verify_routes
            verify_database
            ;;
        0) break ;;
        *) echo "Opción inválida" ;;
    esac
done
```

### 3. Script de Testing Automatizado

```bash
#!/bin/bash
# scripts/test-with-boost.sh

echo "🧪 Ejecutando tests con Laravel-Boost..."

# Pre-test: verificar estado de la aplicación
echo "📊 Estado pre-test..."
php artisan boost:mcp application-info

# Ejecutar tests
echo "🏃‍♂️ Ejecutando tests..."
php artisan test --testsuite=Feature

# Post-test: verificar errores
echo "🔍 Verificando errores post-test..."
php artisan boost:mcp last-error

# Verificar logs generados durante tests
echo "📋 Logs generados durante tests..."
php artisan boost:mcp read-log-entries 10

# Verificar logs del browser si hay tests de navegador
echo "🌐 Logs del browser..."
php artisan boost:mcp browser-logs 5

echo "✅ Testing completado con Laravel-Boost"
```

## Templates de Comandos Artisan

### 1. Comando de Instalación del Paquete

```php
<?php
// src/Console/Commands/InstallExpansionCommand.php

namespace Company\LaravelReactExpansion\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallExpansionCommand extends Command
{
    protected $signature = 'expansion:install {--force : Force installation}';
    protected $description = 'Instala el paquete de expansión con verificaciones de Laravel-Boost';

    public function handle()
    {
        $this->info('🚀 Instalando Laravel React Expansion Package...');

        // Verificar información de la aplicación
        $this->info('📊 Verificando compatibilidad...');
        $this->verifyCompatibility();

        // Publicar archivos
        $this->info('📁 Publicando archivos...');
        $this->publishFiles();

        // Ejecutar migraciones
        $this->info('🗄️ Ejecutando migraciones...');
        $this->runMigrations();

        // Ejecutar seeders
        $this->info('🌱 Ejecutando seeders...');
        $this->runSeeders();

        // Verificar instalación
        $this->info('✅ Verificando instalación...');
        $this->verifyInstallation();

        $this->info('🎉 ¡Instalación completada exitosamente!');
    }

    private function verifyCompatibility()
    {
        // Usar Laravel-Boost para verificar compatibilidad
        $appInfo = json_decode(
            Artisan::output('boost:mcp application-info'), 
            true
        );

        if (version_compare($appInfo['laravel_version'], '12.0', '<')) {
            $this->error('❌ Laravel 12+ es requerido');
            exit(1);
        }

        $this->info('✅ Compatibilidad verificada');
    }

    private function publishFiles()
    {
        $this->call('vendor:publish', [
            '--tag' => 'expansion-config',
            '--force' => $this->option('force')
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'expansion-assets',
            '--force' => $this->option('force')
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'expansion-migrations',
            '--force' => $this->option('force')
        ]);
    }

    private function runMigrations()
    {
        $this->call('migrate');
        
        // Verificar esquema con Laravel-Boost
        Artisan::call('boost:mcp database-schema --filter=expansion');
        $this->info('🗄️ Esquema de base de datos verificado');
    }

    private function runSeeders()
    {
        $this->call('db:seed', ['--class' => 'ExpansionSeeder']);
    }

    private function verifyInstallation()
    {
        // Verificar rutas
        $routes = Artisan::output('boost:mcp list-routes --path=expansion');
        if (empty($routes)) {
            $this->error('❌ Las rutas de expansión no se registraron correctamente');
            exit(1);
        }

        // Verificar configuración
        $config = config('expansion');
        if (empty($config)) {
            $this->error('❌ La configuración de expansión no se cargó correctamente');
            exit(1);
        }

        $this->info('✅ Instalación verificada correctamente');
    }
}
```

### 2. Comando de Diagnóstico

```php
<?php
// src/Console/Commands/DiagnoseExpansionCommand.php

namespace Company\LaravelReactExpansion\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DiagnoseExpansionCommand extends Command
{
    protected $signature = 'expansion:diagnose';
    protected $description = 'Diagnostica el estado del paquete usando Laravel-Boost';

    public function handle()
    {
        $this->info('🔍 Ejecutando diagnóstico del paquete...');

        $this->checkApplicationInfo();
        $this->checkRoutes();
        $this->checkDatabase();
        $this->checkConfiguration();
        $this->checkLogs();
        $this->checkPermissions();

        $this->info('🎯 Diagnóstico completado');
    }

    private function checkApplicationInfo()
    {
        $this->info('📊 Información de la aplicación:');
        Artisan::call('boost:mcp application-info');
        $this->line(Artisan::output());
    }

    private function checkRoutes()
    {
        $this->info('🛣️ Rutas de expansión registradas:');
        Artisan::call('boost:mcp list-routes --path=expansion');
        $output = Artisan::output();
        
        if (empty(trim($output))) {
            $this->warn('⚠️ No se encontraron rutas de expansión');
        } else {
            $this->line($output);
        }
    }

    private function checkDatabase()
    {
        $this->info('🗄️ Tablas de expansión:');
        Artisan::call('boost:mcp database-schema --filter=expansion');
        $this->line(Artisan::output());
    }

    private function checkConfiguration()
    {
        $this->info('⚙️ Configuración de expansión:');
        
        $configs = [
            'expansion.features',
            'expansion.permissions',
            'expansion.media',
            'expansion.dashboard'
        ];

        foreach ($configs as $config) {
            Artisan::call("boost:mcp get-config {$config}");
            $this->line("{$config}: " . Artisan::output());
        }
    }

    private function checkLogs()
    {
        $this->info('📋 Logs recientes:');
        Artisan::call('boost:mcp read-log-entries 5');
        $this->line(Artisan::output());

        $this->info('🌐 Logs del browser:');
        Artisan::call('boost:mcp browser-logs 5');
        $this->line(Artisan::output());
    }

    private function checkPermissions()
    {
        $this->info('🔐 Verificando permisos:');
        
        Artisan::call('boost:mcp tinker --code="
            \$roles = \Spatie\Permission\Models\Role::all();
            \$permissions = \Spatie\Permission\Models\Permission::all();
            echo \"Roles: \" . \$roles->count() . \"\n\";
            echo \"Permisos: \" . \$permissions->count() . \"\n\";
            return ['roles' => \$roles->pluck('name'), 'permissions' => \$permissions->pluck('name')];
        "');
        
        $this->line(Artisan::output());
    }
}
```

## Helpers para Testing con Laravel-Boost

### 1. Trait para Testing

```php
<?php
// src/Testing/LaravelBoostTestHelpers.php

namespace Company\LaravelReactExpansion\Testing;

use Illuminate\Support\Facades\Artisan;

trait LaravelBoostTestHelpers
{
    protected function runBoostQuery(string $query): array
    {
        Artisan::call('boost:mcp database-query', ['query' => $query]);
        return json_decode(Artisan::output(), true) ?: [];
    }

    protected function getLastError(): ?array
    {
        Artisan::call('boost:mcp last-error');
        $output = Artisan::output();
        return empty($output) ? null : json_decode($output, true);
    }

    protected function getBrowserLogs(int $count = 10): array
    {
        Artisan::call('boost:mcp browser-logs', ['entries' => $count]);
        return json_decode(Artisan::output(), true) ?: [];
    }

    protected function getAppLogs(int $count = 10): array
    {
        Artisan::call('boost:mcp read-log-entries', ['entries' => $count]);
        return json_decode(Artisan::output(), true) ?: [];
    }

    protected function tinkerCode(string $code): mixed
    {
        Artisan::call('boost:mcp tinker', ['code' => $code]);
        $output = Artisan::output();
        return json_decode($output, true);
    }

    protected function verifyRouteExists(string $path): bool
    {
        Artisan::call('boost:mcp list-routes', ['path' => $path]);
        $output = Artisan::output();
        return !empty(trim($output));
    }

    protected function verifyTableExists(string $table): bool
    {
        $result = $this->runBoostQuery("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'");
        return !empty($result);
    }

    protected function assertNoRecentErrors(): void
    {
        $error = $this->getLastError();
        $this->assertNull($error, 'Se encontraron errores recientes: ' . json_encode($error));
    }

    protected function assertRouteRegistered(string $routeName): void
    {
        $this->assertTrue(
            $this->verifyRouteExists($routeName),
            "La ruta '{$routeName}' no está registrada"
        );
    }

    protected function assertTableExists(string $tableName): void
    {
        $this->assertTrue(
            $this->verifyTableExists($tableName),
            "La tabla '{$tableName}' no existe"
        );
    }
}
```

### 2. Base Test Case

```php
<?php
// tests/TestCase.php

namespace Company\LaravelReactExpansion\Tests;

use Company\LaravelReactExpansion\Testing\LaravelBoostTestHelpers;
use Orchestra\Testbench\TestCase as Orchestra;
use Company\LaravelReactExpansion\Providers\ExpansionServiceProvider;

abstract class TestCase extends Orchestra
{
    use LaravelBoostTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        $this->artisan('migrate')->run();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ExpansionServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
```

## Scripts de Automatización

### 1. Makefile para Comandos Comunes

```makefile
# Makefile

.PHONY: help install test analyze boost-info boost-routes boost-db boost-logs boost-errors

help: ## Muestra esta ayuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Instala el paquete de expansión
	@echo "🚀 Instalando paquete de expansión..."
	@php artisan expansion:install --force

test: ## Ejecuta todos los tests
	@echo "🧪 Ejecutando tests..."
	@./scripts/test-with-boost.sh

analyze: ## Ejecuta análisis estático
	@echo "🔍 Ejecutando análisis estático..."
	@./vendor/bin/phpstan analyse
	@./vendor/bin/pint --test

boost-info: ## Muestra información de la aplicación
	@php artisan boost:mcp application-info

boost-routes: ## Muestra todas las rutas
	@php artisan boost:mcp list-routes

boost-db: ## Muestra esquema de base de datos
	@php artisan boost:mcp database-schema

boost-logs: ## Muestra logs recientes
	@php artisan boost:mcp read-log-entries 20

boost-errors: ## Muestra último error
	@php artisan boost:mcp last-error

dev-setup: ## Configura entorno de desarrollo
	@./scripts/setup-development.sh

dev-workflow: ## Inicia workflow de desarrollo interactivo
	@./scripts/dev-workflow.sh

diagnose: ## Ejecuta diagnóstico completo
	@php artisan expansion:diagnose

clean: ## Limpia caché y optimizaciones
	@php artisan cache:clear
	@php artisan config:clear
	@php artisan route:clear
	@php artisan view:clear

fresh: ## Resetea base de datos y reinstala
	@php artisan migrate:fresh --seed
	@make install
```

### 2. Package.json Scripts

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "test": "vitest",
    "test:ui": "vitest --ui",
    "lint": "eslint resources/js --ext .ts,.tsx",
    "lint:fix": "eslint resources/js --ext .ts,.tsx --fix",
    "type-check": "tsc --noEmit",
    "boost:browser-logs": "php artisan boost:mcp browser-logs 20",
    "boost:errors": "php artisan boost:mcp last-error",
    "dev:with-boost": "concurrently \"npm run dev\" \"npm run boost:watch\"",
    "boost:watch": "watch -n 5 'npm run boost:browser-logs'",
    "test:with-boost": "npm run test && npm run boost:browser-logs",
    "analyze": "npm run lint && npm run type-check"
  }
}
```

## Workflows de CI/CD con Laravel-Boost

### 1. GitHub Actions

```yaml
# .github/workflows/tests.yml
name: Tests with Laravel-Boost

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v4
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite
    
    - name: Setup Node.js
      uses: actions/setup-node@v4
      with:
        node-version: '18'
        cache: 'npm'
    
    - name: Install PHP dependencies
      run: composer install --no-interaction --prefer-dist --optimize-autoloader
    
    - name: Install NPM dependencies
      run: npm ci
    
    - name: Create Database
      run: php artisan migrate --env=testing
    
    - name: Laravel-Boost Info
      run: php artisan boost:mcp application-info
    
    - name: Verify Routes
      run: php artisan boost:mcp list-routes
    
    - name: Run PHP Tests
      run: vendor/bin/pest --coverage
    
    - name: Check for errors after tests
      run: php artisan boost:mcp last-error
    
    - name: Build Frontend
      run: npm run build
    
    - name: Run Frontend Tests
      run: npm test
    
    - name: Check browser logs
      run: php artisan boost:mcp browser-logs 10
```

## Documentación de Helpers

### Lista de Comandos Laravel-Boost Disponibles

```bash
# Información de la aplicación
php artisan boost:mcp application-info

# Gestión de rutas
php artisan boost:mcp list-routes [--path=expansion] [--method=GET]

# Base de datos
php artisan boost:mcp database-schema [--filter=expansion]
php artisan boost:mcp database-query "SELECT * FROM users LIMIT 5"

# Logs y debugging
php artisan boost:mcp read-log-entries 20
php artisan boost:mcp browser-logs 10
php artisan boost:mcp last-error

# Testing de código
php artisan boost:mcp tinker --code="User::count()"

# Configuración
php artisan boost:mcp get-config expansion.features
php artisan boost:mcp list-available-config-keys

# Búsqueda de documentación
php artisan boost:mcp search-docs "inertia routing"
```

### Patrones de Uso Recomendados

1. **Antes de cada feature**: Verificar estado con `application-info`
2. **Durante desarrollo**: Usar `tinker` para probar código
3. **Después de cambios**: Verificar con `last-error` y `logs`
4. **En testing**: Combinar tests unitarios con verificaciones boost
5. **En debugging**: Usar `browser-logs` para frontend y `read-log-entries` para backend

Esta suite de herramientas integra Laravel-Boost como el núcleo del workflow de desarrollo, proporcionando debugging, testing y análisis continuo durante todo el ciclo de desarrollo del paquete.

## Checklist i18n Obligatorio (añadir a cada PR)

- [ ] Todos los literales visibles usan `useI18n().t('...')` (sin strings hardcodeados en UI).
- [ ] Claves agregadas en `lang/en.json` y `lang/es.json` (orden alfabético y sin duplicados).
- [ ] Tooltips, `sr-only`, `aria-*`, breadcrumbs y menús traducidos.
- [ ] Mensajes del backend (validación/errores) en `lang/en/` y `lang/es/` si aplica.
- [ ] Verificación manual cambiando EN↔ES con el selector del header.
- [ ] Documentación actualizada o referencia a `docs/GUIA_I18N.md`.

### Snippet recomendado (React)

```tsx
import { useI18n } from '@/lib/i18n/I18nProvider';

export function MyButton() {
  const { t } = useI18n();
  return (
    <button aria-label={t('Save')} title={t('Save')}>
      {t('Save')}
    </button>
  );
}
```
