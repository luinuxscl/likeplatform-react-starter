# Migración del Package FCV al Nuevo Sistema de Packages

## ✅ Cambios Realizados

El package FCV ha sido migrado del sistema legacy `extensions.nav` al nuevo sistema de packages con auto-discovery.

### Archivos Creados

1. **`src/Package.php`**
   - Implementa `CustomizationPackageInterface`
   - Define nombre y versión del package
   - Maneja instalación/desinstalación

2. **`config/menu.php`**
   - Define los 3 items de menú:
     - Portería (ShieldCheck icon)
     - Cursos (BookOpen icon)
     - Organizaciones (Building2 icon)
   - Todos en la sección "Operación"

### Archivos Modificados

1. **`src/Providers/FcvServiceProvider.php`**
   - ❌ Eliminado: Código legacy de `Inertia::share(['extensions' => ...])`
   - ✅ Ahora: Los menús se cargan automáticamente desde `config/menu.php`

## 🎯 Ventajas del Nuevo Sistema

### Antes (Sistema Legacy)
```php
// En FcvServiceProvider.php
Inertia::share([
    'extensions' => [
        'nav' => [
            ['title' => 'Portería', 'href' => '/fcv/guard', 'icon' => 'shield-check'],
            // ...
        ],
    ],
]);
```

**Problemas:**
- ❌ Código hardcodeado en el ServiceProvider
- ❌ Iconos como strings sin validación
- ❌ No se integra con sistema de permisos
- ❌ Difícil de mantener

### Ahora (Nuevo Sistema)
```php
// En config/menu.php
return [
    'items' => [
        [
            'section' => 'operation',
            'label' => 'Portería',
            'icon' => 'ShieldCheck',  // Lucide icon validado
            'route' => '/fcv/guard',
            'permission' => null,
            'order' => 10,
        ],
        // ...
    ],
];
```

**Ventajas:**
- ✅ Configuración declarativa
- ✅ Iconos de Lucide validados
- ✅ Sistema de permisos integrado
- ✅ Ordenamiento configurable
- ✅ Auto-discovery automático
- ✅ Caché optimizado

## 🔍 Verificación

```bash
# Verificar que el package es descubierto
php artisan customization:list-packages

# Debería mostrar:
# ┌────────────┬─────────┬───────────────┬───────┬────────┬──────────┐
# │ Nombre     │ Versión │ Estado        │ Menús │ Rutas  │ Permisos │
# ├────────────┼─────────┼───────────────┼───────┼────────┼──────────┤
# │ fcv-access │ 0.1.0   │ ✅ Habilitado │ 3     │ 0      │ 0        │
# └────────────┴─────────┴───────────────┴───────┴────────┴──────────┘
```

## 🎨 Iconos Actualizados

Los iconos han sido actualizados a nombres de Lucide válidos:

| Antes | Ahora | Icono Lucide |
|-------|-------|--------------|
| `shield-check` | `ShieldCheck` | ✅ Válido |
| `book-open` | `BookOpen` | ✅ Válido |
| `building-2` | `Building2` | ✅ Válido |

Ver todos los iconos en: https://lucide.dev/icons/

## 📝 Configuración Actual

### config/menu.php

```php
'items' => [
    [
        'section' => 'operation',      // Sección del menú
        'label' => 'Portería',         // Texto visible
        'icon' => 'ShieldCheck',       // Icono de Lucide
        'route' => '/fcv/guard',       // URL
        'permission' => null,          // Sin permiso (por ahora)
        'order' => 10,                 // Orden de aparición
        'active' => true,              // Habilitado
    ],
    // ... más items
],
```

## 🔐 Próximos Pasos (Opcional)

### 1. Agregar Permisos

Editar `config/menu.php`:

```php
'items' => [
    [
        'section' => 'operation',
        'label' => 'Portería',
        'icon' => 'ShieldCheck',
        'route' => '/fcv/guard',
        'permission' => 'fcv.guard.view',  // ← Agregar permiso
        'order' => 10,
    ],
],

'permissions' => [
    [
        'name' => 'fcv.guard.view',
        'description' => 'Ver módulo de portería',
        'guard_name' => 'web',
    ],
    // ... más permisos
],
```

### 2. Crear Seeder de Permisos

```php
// database/Seeders/FcvPermissionsSeeder.php
use Spatie\Permission\Models\Permission;

$permissions = [
    'fcv.guard.view' => 'Ver módulo de portería',
    'fcv.courses.view' => 'Ver módulo de cursos',
    'fcv.organizations.view' => 'Ver módulo de organizaciones',
];

foreach ($permissions as $name => $description) {
    Permission::firstOrCreate(['name' => $name], ['guard_name' => 'web']);
}
```

### 3. Ejecutar Seeder

```bash
php artisan db:seed --class=FcvPermissionsSeeder
```

## 🧪 Testing

El package ahora se integra con el sistema de testing:

```php
// tests/Feature/Fcv/PackageTest.php
test('fcv package is discovered', function () {
    $service = app(\App\Services\PackageDiscoveryService::class);
    $packages = $service->discover();
    
    expect($packages)->toHaveKey('fcv-access');
});

test('fcv package has correct menu items', function () {
    $service = app(\App\Services\MenuService::class);
    $menus = $service->getMenuItems();
    
    expect($menus['operation'])->toHaveCount(3);
});
```

## 📚 Documentación

Para más información sobre el sistema de packages:
- `../../docs/SISTEMA_PACKAGES_PERSONALIZACION.md` - Documentación completa
- `../../docs/GUIA_RAPIDA_PACKAGES.md` - Guía rápida
- `../../RESUMEN_SISTEMA_PACKAGES.md` - Resumen ejecutivo

## ✅ Checklist de Migración

- [x] Crear `src/Package.php`
- [x] Crear `config/menu.php`
- [x] Actualizar `FcvServiceProvider.php`
- [x] Actualizar iconos a formato Lucide
- [x] Verificar auto-discovery
- [x] Documentar cambios
- [ ] Agregar permisos (opcional)
- [ ] Crear tests específicos (opcional)

## 🎊 Resultado

El package FCV ahora:
- ✅ Se descubre automáticamente
- ✅ Aparece en `php artisan customization:list-packages`
- ✅ Los menús se cargan desde configuración
- ✅ Usa iconos de Lucide validados
- ✅ Está listo para agregar permisos
- ✅ Se integra con el sistema de caché

---

**Fecha de migración**: 2025-10-07  
**Versión del sistema**: 1.0.0  
**Estado**: ✅ Completado
