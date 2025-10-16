# Sistema de Notificaciones

Sistema completo de notificaciones con persistencia en base de datos y centro de notificaciones en la UI.

## Características

- ✅ Notificaciones persistentes en base de datos
- ✅ Centro de notificaciones en el header
- ✅ Contador de notificaciones no leídas
- ✅ Marcar como leída individual o todas
- ✅ Eliminar notificaciones
- ✅ Acciones opcionales con enlaces
- ✅ 4 tipos de notificaciones: info, success, warning, error
- ✅ Timestamps relativos (hace 5 min, hace 2 h, etc.)
- ✅ Interfaz responsive y moderna

## Backend

### Migración

La tabla `notifications` se crea automáticamente con:

```bash
php artisan migrate
```

### Crear Notificaciones

#### Usando GeneralNotification

```php
use App\Notifications\GeneralNotification;

$user->notify(new GeneralNotification(
    title: 'Título de la notificación',
    message: 'Mensaje descriptivo',
    type: 'success', // info, success, warning, error
    actionUrl: '/ruta/opcional',
    actionText: 'Texto del botón'
));
```

#### Ejemplos

**Notificación simple:**
```php
$user->notify(new GeneralNotification(
    title: 'Tarea completada',
    message: 'Tu tarea se ha procesado exitosamente.',
    type: 'success'
));
```

**Notificación con acción:**
```php
$user->notify(new GeneralNotification(
    title: 'Nuevo mensaje',
    message: 'Tienes un nuevo mensaje de Juan.',
    type: 'info',
    actionUrl: '/messages/123',
    actionText: 'Ver mensaje'
));
```

**Notificación de advertencia:**
```php
$user->notify(new GeneralNotification(
    title: 'Acción requerida',
    message: 'Tu suscripción vence en 3 días.',
    type: 'warning',
    actionUrl: '/settings/billing',
    actionText: 'Renovar ahora'
));
```

**Notificación de error:**
```php
$user->notify(new GeneralNotification(
    title: 'Error en el proceso',
    message: 'No se pudo completar la operación. Intenta nuevamente.',
    type: 'error'
));
```

### API Endpoints

Todos los endpoints requieren autenticación:

- `GET /api/notifications` - Listar notificaciones (paginadas)
- `POST /api/notifications/{id}/read` - Marcar como leída
- `POST /api/notifications/read-all` - Marcar todas como leídas
- `DELETE /api/notifications/{id}` - Eliminar notificación
- `DELETE /api/notifications/clear/read` - Eliminar todas las leídas

### Crear Notificación Personalizada

```bash
php artisan make:notification MiNotificacion
```

Ejemplo de notificación personalizada:

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderShipped extends Notification
{
    use Queueable;

    public function __construct(
        public string $orderId,
        public string $trackingNumber
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pedido enviado',
            'message' => "Tu pedido #{$this->orderId} ha sido enviado.",
            'type' => 'success',
            'action_url' => "/orders/{$this->orderId}",
            'action_text' => 'Ver pedido',
            'tracking_number' => $this->trackingNumber,
        ];
    }
}
```

## Frontend

### Componente NotificationCenter

El componente ya está integrado en `app-sidebar-header.tsx` y se muestra automáticamente en todas las páginas autenticadas.

### Hook useNotifications

```typescript
import { useNotifications } from '@/hooks/use-notifications'

function MiComponente() {
  const {
    notifications,      // Array de notificaciones
    unreadCount,        // Contador de no leídas
    hasMore,            // Hay más notificaciones
    loading,            // Estado de carga
    fetchNotifications, // Recargar notificaciones
    markAsRead,         // Marcar como leída (id)
    markAllAsRead,      // Marcar todas como leídas
    deleteNotification, // Eliminar notificación (id)
    clearRead,          // Limpiar todas las leídas
  } = useNotifications()

  return (
    <div>
      <p>Tienes {unreadCount} notificaciones sin leer</p>
    </div>
  )
}
```

### Tipos TypeScript

```typescript
type NotificationType = 'info' | 'success' | 'warning' | 'error'

interface Notification {
  id: string
  type: string
  notifiable_type: string
  notifiable_id: number
  data: {
    title: string
    message: string
    type: NotificationType
    action_url?: string | null
    action_text?: string | null
  }
  read_at: string | null
  created_at: string
  updated_at: string
}
```

## Testing

### Generar Notificaciones de Prueba

```bash
php artisan db:seed --class=NotificationSeeder
```

Esto creará 3 notificaciones de ejemplo para cada usuario.

### Probar en Tinker

```bash
php artisan tinker
```

```php
$user = User::first();

// Crear notificación
$user->notify(new \App\Notifications\GeneralNotification(
    'Test',
    'Mensaje de prueba',
    'info'
));

// Ver notificaciones
$user->notifications;

// Ver no leídas
$user->unreadNotifications;

// Marcar como leída
$user->unreadNotifications->first()->markAsRead();
```

## Personalización

### Cambiar Estilos

Edita `/resources/js/components/notification-center.tsx` para personalizar:

- Colores por tipo de notificación
- Iconos
- Tamaño del dropdown
- Animaciones
- Formato de fecha

### Agregar Canales Adicionales

Puedes enviar notificaciones por múltiples canales:

```php
public function via(object $notifiable): array
{
    return ['database', 'mail', 'broadcast'];
}
```

### Notificaciones en Tiempo Real

Para notificaciones en tiempo real, integra Laravel Echo con Pusher o Laravel Reverb:

1. Instala Laravel Echo y Pusher
2. Configura broadcasting
3. Agrega el canal 'broadcast' a tus notificaciones
4. Escucha eventos en el frontend

## Mejores Prácticas

1. **Usa colas para notificaciones**: Implementa `ShouldQueue` en notificaciones que envían emails
2. **Limpia notificaciones antiguas**: Crea un comando programado para eliminar notificaciones leídas después de X días
3. **Agrupa notificaciones similares**: Evita spam agrupando notificaciones del mismo tipo
4. **Personaliza por usuario**: Permite que los usuarios configuren qué notificaciones recibir
5. **Mantén mensajes concisos**: Los mensajes largos se truncan en la UI

## Troubleshooting

### Las notificaciones no aparecen

1. Verifica que la migración se ejecutó: `php artisan migrate:status`
2. Verifica que el usuario tiene el trait `Notifiable`
3. Revisa los logs: `tail -f storage/logs/laravel.log`
4. Verifica la consola del navegador para errores JS

### Error 404 en endpoints

1. Limpia cache de rutas: `php artisan route:clear`
2. Verifica que las rutas están registradas: `php artisan route:list | grep notification`

### El contador no se actualiza

1. Verifica que axios está configurado correctamente
2. Revisa la consola del navegador
3. Verifica que el endpoint `/api/notifications` retorna datos correctos

## Roadmap

Posibles mejoras futuras:

- [ ] Notificaciones en tiempo real con WebSockets
- [ ] Preferencias de notificación por usuario
- [ ] Notificaciones push en navegador
- [ ] Agrupación de notificaciones similares
- [ ] Búsqueda y filtros en el centro de notificaciones
- [ ] Paginación infinita en el dropdown
- [ ] Sonidos personalizables
- [ ] Plantillas de notificaciones
