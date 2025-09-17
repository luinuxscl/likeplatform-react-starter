# Guía rápida: Sistema de Temas (shadcn/ui)

Esta guía explica cómo funciona el sistema de temas y cómo extenderlo sin romper la compatibilidad con el starter kit (Laravel 12 + Inertia 2 + React 19 + Tailwind 4).

## Visión general

- Backend expone la configuración y el tema activo vía Inertia.
- El modo claro/oscuro lo gestiona el starter (`resources/js/hooks/use-appearance.tsx`) y se aplica con la clase `html.dark`.
- Los "acentos" del tema (primary/accent/ring) se aplican en cliente mediante CSS variables seguras para no interferir con clear/dark.
- SSR inyecta esas variables seguras en `app.blade.php` para evitar FOUC (flash de estilos).

## Archivos clave

- `config/expansion.php`
  - Define `themes.default_theme` y `themes.available_themes`.
- `app/Http/Middleware/HandleInertiaRequests.php`
  - Comparte vía Inertia: `expansion.themes.current`, `available`, `default`.
- `app/Http/Controllers/Expansion/ThemeController.php`
  - Endpoint `PATCH /expansion/themes` para cambiar el tema (persistencia en sesión).
- `resources/views/app.blade.php`
  - Inyección SSR de variables seguras del tema para el primer render.
- `resources/js/hooks/useTheme.tsx`
  - Aplica variables seguras, hace `PATCH` y ofrece `resetDefaults()`.
- `resources/js/components/expansion/themes/ThemeSelector.tsx`
  - UI para seleccionar tema (click-to-apply) y botón "Reset to defaults".
- `resources/js/components/expansion/themes/ThemeLivePreview.tsx`
  - Vista previa en vivo de los acentos del tema.

## Cómo añadir un nuevo tema

1) Edita `config/expansion.php` y agrega una entrada bajo `available_themes`:

```php
'teal' => [
    'name' => 'Teal',
    'colors' => [
        // Tokens usados por el hook (seguros)
        'primary' => 'oklch(...)',
        'primary-foreground' => 'oklch(...)',
        'accent' => 'oklch(...)',
        'accent-foreground' => 'oklch(...)',
        'ring' => 'oklch(...)',

        // Opcionales (para completitud/consistencia)
        'background' => 'oklch(...)',
        'foreground' => 'oklch(...)',
        // ... resto de tokens que use tu diseño
    ],
],
```

2) No es necesario tocar el frontend: `ThemeSelector` lee los temas desde las props compartidas por Inertia.

3) Recarga con `Ctrl+F5` o ejecuta `npm run build` si estás en prod.

## Cómo funciona la aplicación del tema

- Cliente (`useTheme.applyTheme()`):
  - Solo aplica variables seguras: `--primary`, `--primary-foreground`, `--accent`, `--accent-foreground`, `--ring`.
  - No modifica `--background`, `--foreground`, etc., para respetar dark/light.

- Servidor (SSR en `app.blade.php`):
  - Inyecta las mismas variables seguras del tema activo: asegura consistencia en el primer paint.

## Reset a valores por defecto

- El botón "Reset to defaults" en `ThemeSelector`:
  - Limpia `localStorage` y la cookie `appearance`.
  - Aplica `themes.default_theme` y lo persiste en sesión (`PATCH`).

## Tests

- Archivo: `tests/Feature/ThemeManagementTest.php`.
  - Cambio de tema válido y persistencia en sesión.
  - Rechazo de tema inválido.
  - Props `expansion.themes` presentes en la página de Apariencia.
  - Inyección SSR de variables seguras.

Para ejecutar:

```bash
vendor/bin/pest
```

## Buenas prácticas

- Mantén el modo oscuro/claro bajo `use-appearance.tsx`; no lo modifiques.
- No sobreescribas tokens de superficie (background/foreground/card/popover) desde el hook.
- Usa OKLCH para consistencia visual en Tailwind 4.
- Agrega temas en config y evita lógicas de tema "hardcodeadas" en componentes.

## Solución de problemas

- "Mezcla" de light/dark:
  - Asegúrate de no aplicar tokens de superficie en el hook.
  - Verifica que `HandleAppearance` por defecto esté en `dark` si ese es el estándar del proyecto.
- FOUC (parpadeo de acentos):
  - Revisa la inyección SSR en `app.blade.php` y que el tema activo exista en config.
- El selector no muestra temas:
  - Revisa `config/expansion.php` y que `HandleInertiaRequests@share()` esté exponiendo `expansion.themes.available`.
