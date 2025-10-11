# Changelog

Todas las novedades y cambios relevantes del proyecto.

## [Unreleased]

### Configuración de Temas Durante Instalación de Packages
- ✅ **Trait ConfiguresTheme** para ServiceProviders y Commands
- ✅ Método `setDefaultTheme()` para cambiar tema por defecto
- ✅ Método `registerCustomTheme()` para temas personalizados
- ✅ Método `askToChangeTheme()` para confirmación interactiva
- ✅ Actualización automática de `.env`
- ✅ Opciones `--theme=` y `--no-theme` en comandos de instalación
- ✅ Package FCV actualizado con soporte de temas
- ✅ Tests completos (8 tests)
- ✅ Documentación en `docs/guias/package-theme-installation.md`

### Sistema de Temas shadcn/ui
- ✅ **12 temas predefinidos** de shadcn/ui: zinc, slate, stone, gray, neutral, red, rose, orange, green, blue, yellow, violet
- ✅ Cambio de tema en tiempo real sin reload de página
- ✅ Persistencia de preferencia de tema en sesión
- ✅ Compatible con sistema dark/light mode existente
- ✅ CSS variables dinámicas aplicadas selectivamente
- ✅ Hook `useTheme` con TypeScript completo
- ✅ Componente `ThemeSelector` con preview de colores
- ✅ Tests Pest completos (12 tests, 228 assertions)
- ✅ Configuración declarativa en `config/expansion.php`
- ✅ Documentación completa en `docs/sistemas/shadcn-themes.md`

### Documentación
- Reorganización completa de documentación en `/docs` con estructura de subcarpetas
- Nuevo índice general en `docs/README.md`
- Categorización en: guías, sistemas, desarrollo e implementaciones
- Estandarización de nombres de archivos en kebab-case
- README.md principal actualizado con enlaces a documentación

## 0.3.0 - 2025-09-24

- Added hybrid API key management with Laravel Sanctum, including admin panel, self-service UI, audit logging, and sample seed data.
- Added feature and settings tests for API key creation/revocation flows.
- Documented API key workflows and configuration en `docs/api-keys.md`.

## 0.2.0 - 2025-09-23

- Diseño de Dashboard admin inspirado en shadcn/ui con cards theme-aware.
- Sidebar reestructurado en secciones: Plataforma y Administración.
- Seeders de desarrollo: 500 usuarios, 5 admins; fechas no uniformes en 12 meses.
- Comando `app:install --dev` ejecuta seeders adicionales para demo.
- Theming con transición suave `.theme-transition` y switcher de tema.
- Documentación inicial y branding LikePlatform.
- Sistema de auditoría:
  - Migraciones `audit_logs` y `user_sessions` con metadatos de acción, diffs, IPs y sesiones.
  - Trait `HasAuditLogs`, servicios (`AuditLogger`, `UserSessionRecorder`) y middleware `TrackUserActivity`.
  - UI admin `/admin/audit/logs` y `/admin/audit/sessions` con filtros, tablas theme-aware y datos demo.
  - Seeder `DevSampleDataSeeder` ahora genera registros falsos de auditoría y sesiones.
