# Sistema híbrido de API Keys

## Resumen general

El starter kit soporta ahora un flujo híbrido de administración de API keys utilizando Laravel Sanctum. Se contemplan **dos modos de emisión**:

- **Administradores**: pueden crear, listar y revocar claves para cualquier usuario desde `Administración ▸ API Keys`.
- **Usuarios finales**: pueden autogestionar sus propias claves en `Configuración ▸ API Keys` respetando una lista blanca de habilidades.

Todas las operaciones quedan registradas en la auditoría (`audit_logs`) para mantener trazabilidad.

## Habilidades permitidas

La lista de abilities disponible para usuarios finales se define en `config/sanctum.php` mediante `user_abilities`. Los administradores no tienen restricciones y pueden asignar `'*'` para acceso completo.

Ejemplo de configuración por defecto:

```php
return [
    'user_abilities' => [
        'read',
        'write',
        'webhooks',
    ],
];
```

## Interfaz de administración

Ruta: `Administración ▸ API Keys` (`/admin/api-keys`).

Características principales:

- **Filtros** por usuario, texto libre y origen de emisión (`Emitida por: Administrador | Usuario`).
- **Tabla** con columnas de uso (último acceso, IP, User-Agent), expiración y emisor (`created_by`).
- **Generación** de nuevas claves mediante modal; muestra la clave una sola vez con botón _Copiar_.
- **Revocación** con confirmación modal.

> Al copiar una clave se intentará usar la API moderna del portapapeles; si el navegador lo impide se muestra un toast de error.

## Autogestión de usuarios

Ruta: `Configuración ▸ API Keys` (`/settings/api-keys`).

- Formulario con selección de abilities mediante checkboxes basados en la lista blanca.
- Listado paginado de claves personales con botón de revocación.
- Mensaje informativo que advierte que la clave solo se muestra una vez.

## Auditoría

El servicio `App\Services\ApiKeys\ApiKeyManager` reporta las siguientes acciones en `audit_logs`:

- `api_token_created_admin`
- `api_token_created_self`
- `api_token_revoked_admin`
- `api_token_revoked_self`

Los valores sensibles (`token`) nunca se registran gracias a `config/audit.php` y al `AuditLogger`.

## Seeders y datos de demo

`Database\Seeders\DevSampleDataSeeder` genera ejemplos de claves emitidas por administradores y autogestionadas, con metadatos realistas (IPs, user agents, expiraciones).

## Pruebas automáticas

- `tests/Feature/Admin/ApiKeysTest.php`: cubre creación y revocación desde el panel de administración.
- `tests/Feature/Settings/ApiKeysTest.php`: verifica autogestión, normalización de abilities y revocación.

Ejecutar la suite completa:

```bash
./vendor/bin/pest
```

## Recomendaciones para despliegue

1. Ejecutar migraciones (`php artisan migrate`).
2. Revisar `SANCTUM_EXPIRATION` si se necesita caducidad automática.
3. Confirmar que el entorno está servido bajo HTTPS para habilitar `navigator.clipboard` en los botones _Copiar_.

Con esto el starter kit queda listo para integraciones externas controladas y autogestionadas por los usuarios finales.
