# Admin Dashboard

Dashboard administrativo minimalista, extensible y theme-aware.

## Objetivos

- Vista general con KPIs básicos y accesos rápidos.
- Cambios de theme en tiempo real con transición suave.
- Integración total con el sistema de permisos y el stack Inertia/React.

## Estructura

- Controlador: `app/Http/Controllers/Admin/AdminDashboardController.php`
  - Acción: `index()`
  - Carga KPIs y usuarios recientes, cacheados por 90s (clave `admin:dashboard:kpis`).
- Ruta: `routes/admin.php`
  - `GET /admin` → redirect a `admin.dashboard`
  - `GET /admin/dashboard` → `AdminDashboardController@index`
  - Middleware: `auth`, `verified`, `role:admin`.
- Página Inertia: `resources/js/pages/admin/dashboard/index.tsx`
  - KPIs en cards simples (minimal).
  - Accesos rápidos: Usuarios, Roles, Permisos, Opciones.
  - Tabla de usuarios recientes.
  - Widget para cambio de theme: `ThemeSwitcherMini`.
- Widget de theme: `resources/js/components/theme/theme-switcher-mini.tsx`
  - Aplica el theme seleccionado vía `useTheme()`/`applyTheme()`.
  - Añade/remueve `.theme-transition` para una animación suave del cambio de colores.
- Transición global de theme: `resources/css/app.css`
  - Regla:
    ```css
    .theme-transition, .theme-transition * {
      transition: background-color .4s ease, color .4s ease, border-color .4s ease, fill .4s ease, stroke .4s ease;
    }
    ```

## KPIs (MVP)

- `total_users`: total de usuarios.
- `new_users_7d`: usuarios creados en los últimos 7 días.
- `verified_users`: usuarios con email verificado.
- `roles_count`: cantidad de roles.

## Theme-aware (Regla UI)

- Usar tokens de Tailwind/shadcn/ui:
  - `bg-muted`, `text-muted-foreground`, `border-border`, `text-primary`, `bg-primary`, `ring-ring`, etc.
- Evitar colores hardcodeados (e.g. `neutral-*`).
- Preferir componentes de shadcn/ui (`Card`, `Button`, etc.) que ya respetan el tema.
- En Dashboard y vistas admin:
  - Botones principales usan `variant="default"` (toman `--primary`).
  - Tablas usan `bg-muted` en thead, `border-border` en filas y `text-primary` para enlaces.

## Caché

- KPIs cacheados 90s con `Cache::remember('admin:dashboard:kpis', now()->addSeconds(90), fn() => ...)`.
- Para tráfico mayor, ajustar TTL o invalidación por eventos (futuro).

## Extensión futura (ideas)

- Gráficas (usuarios por día/semana) con librería ligera de charts o SVG propio.
- Salud del sistema (jobs fallidos, colas, espacio de disco).
- Auditoría/Activity log.
- Más KPIs (permisos usados, usuarios activos por rol, etc.).

## Testing

- Acceso permitido a admin (`role:admin`).
- Acceso denegado a no-admin.
- Respuesta Inertia incluye estructura de KPIs y `recent_users`.

## Convenciones

- Mantener minimalistas las KPI Cards (sin efectos extra por defecto).
- Aplicar la guía de theme-aware a todas las vistas y formularios admin.
- Documentar cualquier nuevo KPI/Widget en este archivo.
