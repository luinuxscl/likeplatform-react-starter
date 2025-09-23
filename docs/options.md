# Options (Configuración de la App)

Este módulo permite gestionar opciones de configuración persistidas en base de datos y cacheadas para alto rendimiento.

## Modelo y Migración

- Modelo: `App\Models\Option`
- Tabla: `options`
- Campos clave:
  - `key` (string, única)
  - `value` (text, valor serializado como string)
  - `type` (string, tipo lógico: string|text|boolean|integer|json|url|...)
  - `group` (string, agrupación para UI)
  - `description` (text)

Migrar:

```bash
php artisan migrate
```

## Servicio con Caché

- Servicio: `App\Services\Options`
- API:
  - `get(string $key, mixed $default = null): mixed`
  - `set(string $key, mixed $value, ?string $type = null, ?string $group = null, ?string $description = null): void`
  - `forget(string $key): void`
  - `all(): array`
- Cachea cada clave como `option:{key}` con `rememberForever`. `set()` hace `forget()` y vuelve a cachear el valor casteado.

Uso en código (ejemplos):

```php
use App\Services\Options;

$appName = app(Options::class)->get('app.name', config('app.name'));
$timezone = app(Options::class)->get('app.timezone', config('app.timezone'));
```

## Comandos Artisan

- Obtener valor (con default opcional):

```bash
php artisan option:get app.name --default="Mi App"
```

- Establecer valor (tipos soportados: string, text, boolean, integer, json, url, ...):

```bash
php artisan option:set app.name "LikePlatform" --type=string --group=general --description="Nombre visible de la aplicación"
php artisan option:set app.timezone "America/Santiago" --type=string --group=i18n
php artisan option:set app.description "Plataforma moderna y flexible para construir experiencias digitales." --type=text --group=general
```

- Limpiar caché:

```bash
# Clave específica
php artisan option:clear app.name

# Todas las claves
php artisan option:clear
```

## UI de Administración (Inertia)

- Ruta: `GET /admin/options` (permiso `options.view`)
- Guardar: `PUT /admin/options` (permiso `options.update`)
- Página: `resources/js/pages/admin/options/index.tsx`
- Enlace en sidebar: visible si `auth.permissions` incluye `options.view`.

## Permisos

- Seed: `database/seeders/PermissionSeeder.php`
  - `options.view` y `options.update`
- Asignación al rol admin: `AdminRoleSeeder` aplica `syncPermissions(Permission::all())`.
- Rutas protegidas por middleware `permission:...`.

Para resembrar permisos y asegurar asignación:

```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=AdminRoleSeeder
```

## Valores por defecto

Seeder: `database/seeders/OptionSeeder.php`

Valores iniciales:
- `app.name = LikePlatform`
- `app.description = "Plataforma moderna y flexible para construir experiencias digitales."`
- `app.date_format = dd/mm/YYYY`
- `app.timezone = America/Santiago`

Aplicar:

```bash
php artisan db:seed --class=OptionSeeder
```

Si no ves los cambios, limpia caché de opciones:

```bash
php artisan option:clear
```

## Buenas Prácticas

- Evitar leer directamente desde `Option` en el código de negocio; usar siempre `Options` (servicio) para beneficiarse de la caché.
- Definir el _schema_ de campos en un solo lugar (actualmente en `OptionsController`); se puede evolucionar a `config/options.php` para mayor flexibilidad.
- Validar tipos y rangos según corresponda (p.ej. validar `timezone` contra `timezone_identifiers_list()`).
- Incluir tests para view/update y comandos (ya incluidos en `tests/Feature/Admin/OptionsTest.php`).
