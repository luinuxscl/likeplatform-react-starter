# Configuración de Temas Durante la Instalación de Packages

Esta guía explica cómo los packages pueden configurar el tema de la aplicación durante su instalación.

## 📋 Descripción

Los packages pueden sugerir o establecer un tema específico durante su instalación, permitiendo una experiencia visual consistente con la identidad del módulo.

## 🎯 Casos de Uso

- **Packages con identidad de marca** - FCV con colores institucionales azules
- **Temas corporativos** - Aplicar colores de la empresa automáticamente
- **Experiencia consistente** - El usuario obtiene el look & feel correcto desde el inicio

## 🛠️ Implementación en tu Package

### 1. Definir Tema Sugerido

En el archivo de configuración de tu package (`config/mi-package.php`):

```php
<?php

return [
    // Tema sugerido para este package
    'theme' => env('MI_PACKAGE_THEME', 'blue'),
    
    // Otras configuraciones...
];
```

### 2. Usar el Trait en InstallCommand

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

    protected $description = 'Instala el package';

    public function handle(): int
    {
        // ... otras tareas de instalación ...

        // Configurar tema
        $this->configureTheme();

        return self::SUCCESS;
    }

    protected function configureTheme(): void
    {
        if ($this->option('no-theme')) {
            return;
        }

        $suggestedTheme = config('mi-package.theme', 'zinc');

        if ($theme = $this->option('theme')) {
            $this->components?->task("Configurando tema: {$theme}", function () use ($theme) {
                $this->setDefaultTheme($theme, true);
            });
            return;
        }

        if ($this->askToChangeTheme($suggestedTheme, 'Mi Package')) {
            $this->components?->task("Configurando tema: {$suggestedTheme}", function () use ($suggestedTheme) {
                $this->setDefaultTheme($suggestedTheme, true);
            });
        }
    }
}
```

## 📝 Métodos Disponibles del Trait

### `setDefaultTheme(string $theme, bool $updateEnv = true): bool`

Establece el tema por defecto de la aplicación.

**Parámetros:**
- `$theme` - Nombre del tema (zinc, slate, rose, etc.)
- `$updateEnv` - Si debe actualizar el archivo `.env`

**Retorna:** `true` si fue exitoso, `false` si el tema no existe

**Ejemplo:**
```php
$this->setDefaultTheme('blue', true);
```

### `registerCustomTheme(string $key, array $config): void`

Registra un tema personalizado del package.

**Parámetros:**
- `$key` - Clave única del tema
- `$config` - Configuración del tema con `name` y `colors`

**Ejemplo:**
```php
$this->registerCustomTheme('mi-package-theme', [
    'name' => 'Mi Package Theme',
    'colors' => [
        'primary' => 'hsl(210 100% 50%)',
        'primary-foreground' => 'hsl(0 0% 100%)',
        'accent' => 'hsl(150 100% 40%)',
        'accent-foreground' => 'hsl(0 0% 0%)',
        'ring' => 'hsl(210 100% 50%)',
    ],
]);
```

### `askToChangeTheme(string $suggestedTheme, string $packageName): bool`

Pregunta al usuario si desea cambiar el tema.

**Parámetros:**
- `$suggestedTheme` - Tema sugerido
- `$packageName` - Nombre del package para mostrar en el mensaje

**Retorna:** `true` si el usuario acepta, `false` si rechaza

**Ejemplo:**
```php
if ($this->askToChangeTheme('blue', 'FCV')) {
    $this->setDefaultTheme('blue', true);
}
```

## 🚀 Uso desde la Línea de Comandos

### Instalación con tema específico

```bash
php artisan mi-package:install --theme=rose
```

### Instalación sin cambiar tema

```bash
php artisan mi-package:install --no-theme
```

### Instalación interactiva (pregunta al usuario)

```bash
php artisan mi-package:install
```

El comando preguntará:
```
The Mi Package package suggests using the 'blue' theme.
Current theme: zinc
Do you want to change the default theme? (yes/no) [no]:
```

## 🎨 Temas Disponibles

Los packages pueden usar cualquiera de los 12 temas predefinidos de shadcn/ui:

**Neutrales:**
- `zinc` (default)
- `slate`
- `stone`
- `gray`
- `neutral`

**Colores:**
- `red`
- `rose`
- `orange`
- `green`
- `blue`
- `yellow`
- `violet`

## 🔧 Temas Personalizados

Los packages también pueden registrar sus propios temas personalizados:

```php
// En el ServiceProvider del package
public function boot(): void
{
    $this->registerCustomTheme('mi-marca', [
        'name' => 'Mi Marca',
        'colors' => [
            'primary' => 'hsl(210 100% 50%)',
            'primary-foreground' => 'hsl(0 0% 100%)',
            'accent' => 'hsl(150 100% 40%)',
            'accent-foreground' => 'hsl(0 0% 0%)',
            'ring' => 'hsl(210 100% 50%)',
        ],
    ]);
}
```

Luego, en el InstallCommand:

```php
$this->setDefaultTheme('mi-marca', true);
```

## 📊 Ejemplo Completo: Package FCV

El package FCV implementa esta funcionalidad de la siguiente manera:

### 1. Configuración (`packages/fcv/config/fcv.php`)

```php
return [
    'theme' => env('FCV_THEME', 'blue'),
    // ...
];
```

### 2. InstallCommand (`packages/fcv/src/Console/Commands/InstallCommand.php`)

```php
class InstallCommand extends Command
{
    use ConfiguresTheme;

    protected $signature = 'fcv:install 
                            {--dev}
                            {--theme=}
                            {--no-theme}';

    public function handle(): int
    {
        // ... instalación ...
        
        $this->configureTheme();
        
        return self::SUCCESS;
    }

    protected function configureTheme(): void
    {
        if ($this->option('no-theme')) {
            return;
        }

        $suggestedTheme = config('fcv.theme', 'blue');

        if ($theme = $this->option('theme')) {
            $this->components?->task("Configurando tema: {$theme}", function () use ($theme) {
                $this->setDefaultTheme($theme, true);
            });
            return;
        }

        if ($this->askToChangeTheme($suggestedTheme, 'FCV')) {
            $this->components?->task("Configurando tema: {$suggestedTheme}", function () use ($suggestedTheme) {
                $this->setDefaultTheme($suggestedTheme, true);
            });
        }
    }
}
```

### 3. Uso

```bash
# Instalar FCV con tema blue (sugerido)
php artisan fcv:install

# Instalar FCV con tema específico
php artisan fcv:install --theme=rose

# Instalar FCV sin cambiar tema
php artisan fcv:install --no-theme
```

## ✅ Buenas Prácticas

1. **Siempre ofrecer opción `--no-theme`** - Respetar la preferencia del usuario
2. **Usar tema sugerido configurable** - Permitir override via `.env`
3. **Preguntar antes de cambiar** - No forzar el cambio sin confirmación
4. **Validar tema existe** - Usar `setDefaultTheme()` que valida automáticamente
5. **Documentar el tema sugerido** - En el README del package

## 🧪 Testing

```php
it('can set theme during installation', function () {
    Artisan::call('mi-package:install', [
        '--theme' => 'blue',
        '--no-interaction' => true,
    ]);
    
    $envContent = File::get(base_path('.env'));
    expect($envContent)->toContain('EXPANSION_DEFAULT_THEME=blue');
});
```

## 🔗 Referencias

- [Sistema de Temas shadcn/ui](shadcn-themes.md)
- [Sistema de Packages](../sistemas/packages-personalizacion.md)
- [Guía de Temas](temas.md)

---

**Versión**: 1.0.0  
**Última actualización**: 2025-10-11  
**Estado**: ✅ Implementado
