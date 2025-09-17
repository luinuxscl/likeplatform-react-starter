# Laravel 12 + React 19 Starter Kit (Extensión)

Proyecto base Laravel 12 con Inertia 2, React 19 y Tailwind 4, extendido con un sistema de temas (shadcn/ui) y utilidades de desarrollo.

## Tecnologías

- Laravel 12 (PHP 8.3)
- Inertia 2 + React 19 (TypeScript)
- Tailwind CSS 4 + shadcn/ui + Radix UI
- Pest 4 para testing

## Estructura relevante

- `config/expansion.php`: configuración de la capa de expansión (temas, etc.)
- `app/Http/Middleware/HandleInertiaRequests.php`: comparte props de Inertia (incluye `expansion.themes`)
- `app/Http/Middleware/HandleAppearance.php`: gestiona modo `light/dark/system` (por defecto: `dark`)
- `app/Http/Controllers/Expansion/ThemeController.php`: endpoint `PATCH /expansion/themes`
- `resources/views/app.blade.php`: inyección SSR de variables seguras del tema
- Frontend
  - `resources/js/hooks/use-appearance.tsx`: modo claro/oscuro (starter)
  - `resources/js/hooks/useTheme.tsx`: aplica tokens de tema (seguros) y `resetDefaults()`
  - `resources/js/components/expansion/themes/ThemeSelector.tsx`: selector de tema (click-to-apply)
  - `resources/js/components/expansion/themes/ThemeLivePreview.tsx`: vista previa en vivo
  - `resources/js/pages/settings/appearance.tsx`: integra selector + preview
- Tests
  - `tests/Feature/ThemeManagementTest.php`

## Documentación

- `docs/GUIA_TEMAS.md`: guía breve del sistema de temas y cómo extenderlo
- `docs/DIRECTRICES_AGENTES_IA.md`: directrices para trabajo con agentes de IA
- `docs/ESTRATEGIA_GIT_LARAVEL_BOOST.md` / `docs/ESTRATEGIA_GIT_MVP_LOCAL.md`: flujos de trabajo y ramas
- `docs/ROADMAP_*`: prioridades y roadmap

## Puesta en marcha

1) Dependencias

```bash
composer install
npm ci
```

2) Variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

3) Migraciones y assets

```bash
php artisan migrate
npm run dev   # desarrollo (Vite)
# o
npm run build # producción
```

4) Testing

```bash
vendor/bin/pest
```

## Sistema de Temas (resumen)

- Temas definidos en `config/expansion.php` → `themes.available_themes`
- Props compartidas: `expansion.themes.{current, available, default}`
- Cambiar tema: `PATCH /expansion/themes` (sesión)
- El hook `useTheme` aplica solo variables seguras: `--primary`, `--primary-foreground`, `--accent`, `--accent-foreground`, `--ring` para no interferir con `light/dark`.
- `Reset to defaults` limpia preferencias de cliente y restablece el tema por defecto.

## Comandos útiles

- Desarrollo: `npm run dev`
- Build: `npm run build`
- Tests: `vendor/bin/pest`
- Limpiar cachés Laravel:

```bash
php artisan config:clear && php artisan route:clear && php artisan view:clear
```

## Notas

- No se modifican archivos del starter base; la extensión se implementa de forma no intrusiva.
- Si agregas nuevos temas, sigue la guía en `docs/GUIA_TEMAS.md`.
