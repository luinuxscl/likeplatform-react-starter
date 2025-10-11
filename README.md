# LikePlatform React Starter

Starter kit moderno con Laravel 12 + React 19 (Inertia v2), Tailwind 4 y shadcn/ui. Incluye dashboard admin, roles/permisos (spatie), sistema de packages extensible, theming y comando de instalación para desarrollo.

## 📚 Documentación

**[Ver documentación completa →](docs/README.md)**

### Documentación destacada

- **[Guía rápida de Packages](docs/guias/packages-rapida.md)** - Crear packages de expansión
- **[Sistema de Packages](docs/sistemas/packages-personalizacion.md)** - Documentación completa
- **[Sistema de Temas shadcn/ui](docs/sistemas/shadcn-themes.md)** - 12 temas predefinidos
- **[Sistema de Settings](docs/sistemas/settings-final.md)** - Configuración de packages
- **[Laravel Boost Guidelines](docs/desarrollo/laravel-boost-guidelines.md)** - Directrices de desarrollo

## Stack

- PHP 8.3 · Laravel 12
- Inertia v2 · React 19 · TypeScript
- Tailwind CSS 4 · shadcn/ui · Radix UI
- Pest 4 (tests)

## Características

### Core
- Dashboard Admin con cards estilo shadcn (theme-aware)
- Gestión de usuarios, roles y permisos con `spatie/laravel-permission`
- Menú dinámico con secciones Plataforma, Operación y Administración
- Sistema de auditoría completo con logs y sesiones
- API Keys con Laravel Sanctum

### Sistemas Extensibles
- **Sistema de Packages**: Auto-discovery, menús dinámicos, permisos
- **Sistema de Settings**: Configuración UI auto-generada desde schemas
- **Sistema de Temas**: 12 temas shadcn/ui + light/dark mode
- **Internacionalización**: Soporte multi-idioma (ES/EN)

### Developer Experience
- Comando de instalación integral (`app:install --dev`)
- Seeders de desarrollo realistas (500 usuarios, distribución 12 meses)
- Tests completos con Pest 4
- Hot reload con Vite
- Laravel Boost MCP integration

## Instalación rápida

1) Dependencias

```bash
composer install
npm ci
```

2) Entorno

```bash
cp .env.example .env
php artisan key:generate
```

3) Base de datos y assets

```bash
# Desarrollo (recrea DB, ejecuta seeders base + datos de ejemplo)
php artisan app:install --dev

# Frontend
npm run dev   # desarrollo
# o
npm run build # producción
```

4) Tests

```bash
vendor/bin/pest
```

## Estructura relevante

- Backend
  - `app/Console/Commands/AppInstall.php` · flujo de instalación (incluye `--dev`).
  - `database/seeders/DevSampleDataSeeder.php` · 500 usuarios, 5 admins, distribución no uniforme 12 meses.
  - `database/seeders/{PermissionSeeder,AdminRoleSeeder,AdminUserSeeder}.php` · permisos/rol/admin inicial.
- Frontend
  - `resources/js/pages/admin/dashboard/index.tsx` · dashboard estilo shadcn.
  - `resources/js/components/ui/{card.tsx,badge.tsx}` · componentes base extendidos (CardAction/Badge).
  - `resources/js/components/app-sidebar.tsx` · sidebar con secciones y iconografía.

## Convenciones de UI

- Usar tokens/theme de Tailwind y shadcn/ui: `bg-muted`, `text-muted-foreground`, `border-border`, `text-primary`, etc.
- Evitar colores hardcodeados.
- Preferir componentes shadcn/ui para respetar el tema activo.

## Autor

- Luis Sepúlveda (luinuxSCL)
- GitHub: https://github.com/luinuxscl

## Licencia

MIT
