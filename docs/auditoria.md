# Sistema de auditoría

Esta guía describe cómo funciona el sistema de auditoría incluido en el starter kit, qué datos registra y cómo consultarlos desde la interfaz de administración.

## Objetivos

- Registrar cambios relevantes sobre modelos auditados.
- Capturar sesiones de usuarios (inicio, última actividad, cierre, contexto del dispositivo).
- Exponer la información únicamente a usuarios con rol `admin`.

## Componentes principales

- **Modelo `App\Models\AuditLog`**: guarda la acción, el modelo afectado (`auditable_type`/`auditable_id`), datos anteriores/nuevos (`old_values`, `new_values`), metadata adicional, IP, user agent y URL asociada.
- **Servicio `App\Services\Audit\AuditLogger`**: punto central para crear entradas en `audit_logs`, filtrando campos sensibles definidos en `config/audit.php`.
- **Trait `App\Traits\HasAuditLogs`**: engancha eventos `created`, `updated`, `deleted` y `restored` (si aplica) para cualquier modelo que desee auditabilidad.
- **Modelo `App\Models\UserSession`** y **servicio `App\Services\Audit\UserSessionRecorder`**: registran sesiones autenticadas, incluyendo dispositivo, navegador, IP y timestamps (`login_at`, `last_activity_at`, `logout_at`).
- **Middleware `App\Http\Middleware\TrackUserActivity`**: actualiza `last_activity_at` en cada request de un usuario autenticado.
- **Listeners `RecordUserLogin` / `RecordUserLogout`**: reaccionan a los eventos de login/logout de Laravel para iniciar/cerrar sesiones.

## Configuración

Archivo: `config/audit.php`

```php
return [
    'models' => [
        App\Models\User::class,
    ],
    'exclude' => [
        'password',
        'remember_token',
    ],
    'retention_days' => env('AUDIT_RETENTION_DAYS', 365),
];
```

- **`models`**: define los modelos auditables por defecto. Cada modelo puede añadir el trait `HasAuditLogs` para engancharse.
- **`exclude`**: campos que no deben almacenarse en los diffs.
- **`retention_days`**: valor destinado a procesos futuros de limpieza (no implementado aún).

## Habilitar auditoría en un modelo

```php
namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasAuditLogs;
}
```

El trait escucha los eventos del ciclo de vida y delega al `AuditLogger`. Los campos excluidos se respetan automáticamente.

## Captura de sesiones

- El listener `RecordUserLogin` crea/actualiza un registro en `user_sessions` cuando un usuario inicia sesión.
- El middleware `TrackUserActivity` refresca el campo `last_activity_at` con cada request.
- El listener `RecordUserLogout` marca `logout_at` cuando el usuario cierra sesión.

## Interfaz de administración

Rutas protegidas por `role:admin` en `routes/admin.php`:

- `/admin/audit/logs`: listado paginado con filtros por acción, modelo, usuario, rango de fechas y búsqueda (URL/IP/User Agent). Permite expandir detalles (valores antiguos/nuevos, metadata).
- `/admin/audit/sessions`: muestra sesiones activas y recientes, con filtros por usuario, estado (activas/cerradas) y búsqueda (IP/dispositivo/navegador).

Los enlaces están disponibles en el sidebar de la aplicación (`Auditoría - Registros`, `Auditoría - Sesiones`).

## Datos ficticios

El seeder `Database\Seeders\DevSampleDataSeeder` genera:

- 500 usuarios demo con distribución temporal realista.
- ~60 registros de auditoría ficticios con acciones variadas.
- ~80 sesiones de usuario con dispositivos y navegadores simulados.

Ejecutar `php artisan app:install --dev` en entornos locales reinicia la base y pobla estas tablas.

## Pruebas

Test principal: `tests/Feature/Admin/AuditLogTest.php`.

- Verifica que el ciclo de vida de un `User` (create/update/delete) genera entradas correspondientes en `audit_logs`.

Ejecuta `vendor/bin/pest` para correr la suite.

## Requisitos previos

- Laravel 12, PHP 8.3.6.
- Inertia v2, React 19, Tailwind 4.
- Paquete `spatie/laravel-permission` para roles/permissions.

## Próximos pasos sugeridos

- Implementar limpieza automática de logs según `retention_days` (job programado).
- Añadir soporte para exportar registros (CSV/JSON) desde la UI.
- Registrar auditoría en más modelos mediante el trait `HasAuditLogs`.
