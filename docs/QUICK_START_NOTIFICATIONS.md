# Quick Start: Sistema de Notificaciones

## 🚀 Inicio Rápido en 5 Minutos

### 1. Configurar Variables de Entorno

Copia estas variables a tu `.env`:

```env
# Email
MAIL_MAILER=log  # Cambiar a smtp en producción
MAIL_FROM_ADDRESS="noreply@tuapp.com"
MAIL_FROM_NAME="${APP_NAME}"

# Broadcasting
BROADCAST_CONNECTION=reverb

# Reverb
REVERB_APP_ID=demo-app
REVERB_APP_KEY=demo-key
REVERB_APP_SECRET=demo-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite (para frontend)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# Colas
QUEUE_CONNECTION=database
```

### 2. Iniciar Servicios

```bash
# Terminal 1: Queue worker
php artisan queue:work

# Terminal 2: Reverb (WebSocket)
php artisan reverb:start

# Terminal 3: Vite (frontend)
npm run dev

# Terminal 4: Laravel server
php artisan serve
```

### 3. Enviar Tu Primera Notificación

```php
use App\Notifications\GeneralNotification;

$user = User::first();

// Notificación completa (BD + Email + Tiempo Real)
$user->notify(new GeneralNotification(
    title: '¡Bienvenido!',
    message: 'Gracias por unirte a nuestra plataforma.',
    type: 'success',
    actionUrl: '/dashboard',
    actionText: 'Ir al Dashboard',
    sendEmail: true,
    broadcast: true
));
```

### 4. Ver el Resultado

1. **En la app**: Verás un toast automático en tiempo real
2. **En el dropdown**: La notificación aparecerá en el centro de notificaciones
3. **En el log**: El email se guardará en `storage/logs/laravel.log`

---

## 📝 Ejemplos Comunes

### Notificación Simple (Solo BD)

```php
$user->notify(new GeneralNotification(
    title: 'Cambios guardados',
    message: 'Tu perfil ha sido actualizado.',
    type: 'success'
));
```

### Notificación con Email

```php
$user->notify(new GeneralNotification(
    title: 'Nuevo pedido',
    message: 'Tu pedido #1234 ha sido confirmado.',
    type: 'info',
    actionUrl: '/orders/1234',
    actionText: 'Ver Pedido',
    sendEmail: true  // ← Envía email
));
```

### Notificación de Error

```php
$user->notify(new GeneralNotification(
    title: 'Error en el pago',
    message: 'No se pudo procesar tu tarjeta. Por favor, intenta nuevamente.',
    type: 'error',
    actionUrl: '/settings/billing',
    actionText: 'Actualizar Método de Pago',
    sendEmail: true,
    broadcast: true
));
```

### Notificación de Advertencia

```php
$user->notify(new GeneralNotification(
    title: 'Suscripción por vencer',
    message: 'Tu suscripción vence en 3 días.',
    type: 'warning',
    actionUrl: '/settings/subscription',
    actionText: 'Renovar Ahora',
    sendEmail: true
));
```

---

## 🎨 Preview de Emails

Visita estas URLs en tu navegador (solo en desarrollo):

- http://localhost:8000/email-preview/info
- http://localhost:8000/email-preview/success
- http://localhost:8000/email-preview/warning
- http://localhost:8000/email-preview/error

---

## 🧪 Probar con Seeder

```bash
# Crear notificaciones de ejemplo
php artisan db:seed --class=NotificationSeeder
```

Esto creará 3 notificaciones para cada usuario:
1. Bienvenida (con email)
2. Nueva funcionalidad (solo BD)
3. Actualización pendiente (con email)

---

## 🐛 Troubleshooting

### No veo notificaciones en tiempo real

1. Verifica que Reverb esté corriendo: `php artisan reverb:start`
2. Verifica las variables de entorno en `.env`
3. Abre la consola del navegador y busca errores de WebSocket
4. Verifica que `broadcast: true` en la notificación

### Los emails no se envían

1. Verifica `MAIL_MAILER` en `.env`
2. Si es `log`, busca en `storage/logs/laravel.log`
3. Verifica que el queue worker esté corriendo
4. Verifica que `sendEmail: true` en la notificación

### Las notificaciones no se encolan

1. Verifica que el queue worker esté corriendo: `php artisan queue:work`
2. Verifica `QUEUE_CONNECTION` en `.env`
3. Revisa trabajos fallidos: `php artisan queue:failed`

### Error de conexión WebSocket

1. Verifica que el puerto 8080 esté libre
2. Verifica las variables `VITE_REVERB_*` en `.env`
3. Reinicia Vite: `npm run dev`
4. Limpia caché del navegador

---

## 📚 Más Información

- **Implementación completa**: `docs/NOTIFICATION_IMPLEMENTATION.md`
- **Plan de 7 fases**: `docs/NOTIFICATION_SYSTEM_PLAN.md`
- **Comparación antes/después**: `docs/NOTIFICATION_COMPARISON.md`
- **Documentación básica**: `docs/NOTIFICATIONS.md`

---

## 💡 Tips

1. **Desarrollo**: Usa `MAIL_MAILER=log` para ver emails en el log
2. **Producción**: Cambia a `smtp`, `mailgun`, `ses`, etc.
3. **Performance**: El queue worker procesa emails en background
4. **Tiempo real**: Reverb es gratis y self-hosted (no necesitas Pusher)
5. **Testing**: Ejecuta `php artisan test` para verificar todo funciona

---

## 🎉 ¡Listo!

Ya tienes un sistema de notificaciones profesional funcionando. Ahora puedes:

✅ Enviar notificaciones desde cualquier parte de tu app  
✅ Notificar por email, BD y tiempo real  
✅ Personalizar mensajes según el tipo  
✅ Escalar sin problemas con el sistema de colas  

**¿Necesitas más funcionalidades?** Consulta el plan completo en `NOTIFICATION_SYSTEM_PLAN.md` para implementar:
- Logging de notificaciones (Spatie)
- Preferencias de usuario
- SMS, Slack, Telegram
- Sistema de templates
