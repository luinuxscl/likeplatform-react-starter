# Changelog

Todas las novedades y cambios relevantes del proyecto.

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
