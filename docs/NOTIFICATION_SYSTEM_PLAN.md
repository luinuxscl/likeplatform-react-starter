# Plan: Sistema de Notificaciones Completo para la App

## 📊 Estado Actual

### ✅ Ya Implementado
- ✅ Tabla `notifications` en base de datos
- ✅ `GeneralNotification` básica con tipos (info, success, warning, error)
- ✅ API REST completa para gestión de notificaciones
- ✅ `NotificationCenter` en UI con dropdown
- ✅ Hook `useNotifications` para frontend
- ✅ Integración en header del sidebar

### ❌ Faltante
- ❌ Notificaciones por email
- ❌ Notificaciones en tiempo real (WebSocket/Broadcasting)
- ❌ Notificaciones SMS
- ❌ Notificaciones Slack/Discord/Telegram
- ❌ Sistema de colas para notificaciones
- ❌ Logging de notificaciones enviadas
- ❌ Preferencias de usuario (qué notificaciones recibir)
- ❌ Plantillas de notificaciones reutilizables
- ❌ Rate limiting y throttling

---

## 🎯 Objetivos del Sistema Completo

1. **Multi-canal**: Email, Database, Broadcast, SMS, Slack, etc.
2. **Tiempo real**: Notificaciones instantáneas con WebSocket
3. **Escalable**: Uso de colas para procesamiento asíncrono
4. **Auditable**: Log completo de todas las notificaciones enviadas
5. **Personalizable**: Usuarios pueden configurar sus preferencias
6. **Testeable**: Tests automatizados para cada canal
7. **Mantenible**: Código limpio y bien documentado

---

## 📦 Packages Recomendados

### Oficiales Laravel
- ✅ **laravel/framework** - Sistema de notificaciones nativo (ya instalado)
- 🔴 **laravel/reverb** - WebSocket server oficial para broadcasting en tiempo real
- 🔴 **laravel/horizon** - Dashboard para monitorear colas (opcional pero recomendado)

### Spatie (Confiables y mantenidos)
- 🔴 **spatie/laravel-notification-log** - Log automático de todas las notificaciones enviadas
- 🟡 **spatie/laravel-slack-alerts** - Alertas rápidas a Slack (opcional)

### Community Channels
- 🟡 **laravel-notification-channels/telegram** - Notificaciones a Telegram
- 🟡 **laravel-notification-channels/discord** - Notificaciones a Discord
- 🟡 **laravel-notification-channels/fcm** - Firebase Cloud Messaging (push móvil)

### Servicios de Email/SMS
- 🔴 **Vonage (Nexmo)** - SMS (soporte nativo en Laravel)
- 🟡 **AWS SES** - Email masivo económico
- 🟡 **Postmark** - Email transaccional rápido
- 🟡 **Mailgun** - Email con tracking avanzado

**Leyenda:**
- 🔴 Alta prioridad / Muy recomendado
- 🟡 Media prioridad / Según necesidad
- ⚪ Baja prioridad / Opcional

---

## 🗺️ Plan de Implementación por Fases

### **FASE 1: Notificaciones por Email** (Prioridad: ALTA)
**Duración estimada:** 2-3 horas

#### Tareas:
1. ✅ Configurar servicio de email (SMTP, Mailgun, SES, etc.)
2. ✅ Extender `GeneralNotification` para soportar canal `mail`
3. ✅ Crear método `toMail()` con plantilla HTML
4. ✅ Implementar notificaciones Markdown
5. ✅ Crear plantillas personalizadas de email
6. ✅ Configurar logo y branding en emails
7. ✅ Implementar preview de emails en desarrollo
8. ✅ Tests para notificaciones por email

#### Archivos a crear/modificar:
```
app/Notifications/GeneralNotification.php          # Agregar toMail()
resources/views/vendor/notifications/             # Plantillas custom
config/mail.php                                    # Configuración email
tests/Feature/Notifications/EmailTest.php         # Tests
```

#### Ejemplo de implementación:
```php
public function via(object $notifiable): array
{
    return ['database', 'mail']; // Multi-canal
}

public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject($this->title)
        ->greeting('¡Hola!')
        ->line($this->message)
        ->when($this->actionUrl, function($mail) {
            return $mail->action($this->actionText, $this->actionUrl);
        })
        ->line('Gracias por usar nuestra aplicación.');
}
```

---

### **FASE 2: Sistema de Colas** (Prioridad: ALTA)
**Duración estimada:** 1-2 horas

#### Tareas:
1. ✅ Configurar driver de colas (database, redis, sqs)
2. ✅ Implementar `ShouldQueue` en notificaciones
3. ✅ Configurar workers y supervisord
4. ✅ Implementar retry logic y failed jobs
5. ✅ Monitoreo de colas con Horizon (opcional)
6. ✅ Rate limiting para evitar spam

#### Archivos a crear/modificar:
```
config/queue.php                                   # Configuración colas
app/Notifications/GeneralNotification.php          # Implementar ShouldQueue
database/migrations/xxxx_create_jobs_table.php     # Migración jobs
database/migrations/xxxx_create_failed_jobs.php    # Failed jobs
```

#### Ejemplo:
```php
class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60]; // Retry delays

    public function viaConnections(): array
    {
        return [
            'mail' => 'redis',      // Email por redis
            'database' => 'sync',   // Database síncrono
        ];
    }
}
```

---

### **FASE 3: Notificaciones en Tiempo Real** (Prioridad: ALTA)
**Duración estimada:** 3-4 horas

#### Tareas:
1. ✅ Instalar y configurar Laravel Reverb
2. ✅ Configurar broadcasting en `config/broadcasting.php`
3. ✅ Agregar canal `broadcast` a notificaciones
4. ✅ Instalar Laravel Echo en frontend
5. ✅ Crear listener de notificaciones en React
6. ✅ Implementar toast automático para notificaciones en tiempo real
7. ✅ Configurar canales privados y autenticación
8. ✅ Tests de broadcasting

#### Packages necesarios:
```bash
composer require laravel/reverb
npm install --save laravel-echo pusher-js
```

#### Archivos a crear/modificar:
```
config/broadcasting.php                            # Configuración
app/Notifications/GeneralNotification.php          # Agregar toBroadcast()
resources/js/lib/echo.ts                          # Configurar Echo
resources/js/hooks/use-realtime-notifications.ts  # Hook para escuchar
routes/channels.php                                # Autorización canales
```

#### Ejemplo frontend:
```typescript
// Hook para notificaciones en tiempo real
export function useRealtimeNotifications() {
  const { fetchNotifications } = useNotifications()
  const { user } = usePage().props.auth

  useEffect(() => {
    window.Echo.private(`App.Models.User.${user.id}`)
      .notification((notification) => {
        // Mostrar toast automático
        toast({
          title: notification.title,
          description: notification.message,
          variant: notification.type === 'error' ? 'destructive' : 'default'
        })
        
        // Actualizar lista de notificaciones
        fetchNotifications()
      })

    return () => {
      window.Echo.leave(`App.Models.User.${user.id}`)
    }
  }, [user.id])
}
```

---

### **FASE 4: Logging de Notificaciones** (Prioridad: MEDIA)
**Duración estimada:** 1-2 horas

#### Tareas:
1. ✅ Instalar `spatie/laravel-notification-log`
2. ✅ Configurar logging automático
3. ✅ Crear dashboard de notificaciones enviadas
4. ✅ Implementar búsqueda y filtros
5. ✅ Prevenir duplicados con `wasAlreadySentTo()`
6. ✅ Reportes de notificaciones

#### Package:
```bash
composer require spatie/laravel-notification-log
php artisan vendor:publish --tag="notification-log-migrations"
php artisan migrate
```

#### Uso:
```php
use Spatie\NotificationLog\Models\NotificationLogItem;

// Prevenir duplicados
public function shouldSend($notifiable, $channel): bool
{
    return ! NotificationLogItem::wasAlreadySentTo(
        notifiable: $notifiable,
        notificationType: static::class,
        hoursAgo: 24
    );
}

// Consultar historial
$logs = NotificationLogItem::query()
    ->forNotifiable($user)
    ->whereNotificationType(GeneralNotification::class)
    ->get();
```

---

### **FASE 5: Preferencias de Usuario** (Prioridad: MEDIA)
**Duración estimada:** 2-3 horas

#### Tareas:
1. ✅ Crear migración para tabla `notification_preferences`
2. ✅ Crear modelo `NotificationPreference`
3. ✅ Crear UI para configurar preferencias
4. ✅ Implementar lógica en método `via()` de notificaciones
5. ✅ Permitir desactivar canales específicos
6. ✅ Implementar frecuencia de notificaciones (instant, daily digest, weekly)

#### Archivos a crear:
```
database/migrations/xxxx_create_notification_preferences.php
app/Models/NotificationPreference.php
app/Http/Controllers/NotificationPreferenceController.php
resources/js/pages/settings/notifications.tsx
```

#### Estructura de preferencias:
```php
// Tabla notification_preferences
Schema::create('notification_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('notification_type'); // GeneralNotification, OrderShipped, etc.
    $table->json('channels'); // ['mail', 'database', 'broadcast']
    $table->string('frequency')->default('instant'); // instant, daily, weekly
    $table->boolean('enabled')->default(true);
    $table->timestamps();
});
```

#### Ejemplo de uso:
```php
public function via(object $notifiable): array
{
    $preference = $notifiable->notificationPreferences()
        ->where('notification_type', static::class)
        ->first();

    if (!$preference || !$preference->enabled) {
        return [];
    }

    return $preference->channels ?? ['database'];
}
```

---

### **FASE 6: Canales Adicionales** (Prioridad: BAJA)
**Duración estimada:** Variable según canal

#### Opción A: SMS (Vonage)
```bash
composer require laravel/vonage-notification-channel
```

```php
public function toVonage($notifiable)
{
    return (new VonageMessage)
        ->content($this->message);
}
```

#### Opción B: Slack
```bash
composer require laravel/slack-notification-channel
```

```php
public function toSlack($notifiable)
{
    return (new SlackMessage)
        ->content($this->message)
        ->attachment(function ($attachment) {
            $attachment->title($this->title);
        });
}
```

#### Opción C: Telegram
```bash
composer require laravel-notification-channels/telegram
```

```php
public function toTelegram($notifiable)
{
    return TelegramMessage::create()
        ->to($notifiable->telegram_user_id)
        ->content($this->message);
}
```

---

### **FASE 7: Plantillas y Sistema de Templates** (Prioridad: BAJA)
**Duración estimada:** 3-4 horas

#### Tareas:
1. ✅ Crear tabla `notification_templates`
2. ✅ Sistema de variables dinámicas ({{user.name}}, {{amount}}, etc.)
3. ✅ Editor de plantillas en admin
4. ✅ Preview de plantillas
5. ✅ Versionado de plantillas
6. ✅ Plantillas multi-idioma

#### Estructura:
```php
Schema::create('notification_templates', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique(); // welcome_email, order_shipped
    $table->string('name');
    $table->text('subject');
    $table->text('body');
    $table->json('variables'); // ['user.name', 'order.id']
    $table->json('channels'); // ['mail', 'database']
    $table->boolean('active')->default(true);
    $table->timestamps();
});
```

---

## 🔧 Configuración Recomendada

### `.env` para Producción
```env
# Queue
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tuapp.com
MAIL_FROM_NAME="${APP_NAME}"

# Broadcasting (Reverb)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# SMS (Vonage) - Opcional
VONAGE_KEY=your-key
VONAGE_SECRET=your-secret
VONAGE_SMS_FROM=YourApp
```

---

## 📈 Cronograma Sugerido

### Semana 1: Fundamentos
- Día 1-2: FASE 1 - Email notifications
- Día 3: FASE 2 - Sistema de colas
- Día 4-5: FASE 3 - Broadcasting en tiempo real

### Semana 2: Mejoras
- Día 1-2: FASE 4 - Logging
- Día 3-4: FASE 5 - Preferencias de usuario
- Día 5: Testing y documentación

### Semana 3: Extras (Opcional)
- FASE 6: Canales adicionales según necesidad
- FASE 7: Sistema de templates (si se requiere)

---

## 🧪 Testing Strategy

### Tests a Implementar
```php
// tests/Feature/Notifications/EmailNotificationTest.php
public function test_sends_email_notification()
{
    Notification::fake();
    
    $user = User::factory()->create();
    $user->notify(new GeneralNotification('Test', 'Message', 'info'));
    
    Notification::assertSentTo($user, GeneralNotification::class);
}

// tests/Feature/Notifications/BroadcastNotificationTest.php
public function test_broadcasts_notification()
{
    Event::fake();
    
    $user = User::factory()->create();
    $user->notify(new GeneralNotification('Test', 'Message', 'info'));
    
    Event::assertDispatched(NotificationSent::class);
}

// tests/Feature/Notifications/QueuedNotificationTest.php
public function test_queues_notification()
{
    Queue::fake();
    
    $user = User::factory()->create();
    $user->notify(new GeneralNotification('Test', 'Message', 'info'));
    
    Queue::assertPushed(SendQueuedNotifications::class);
}
```

---

## 📊 Métricas a Monitorear

1. **Tasa de entrega**: % de notificaciones enviadas exitosamente
2. **Tiempo de procesamiento**: Tiempo promedio de envío
3. **Tasa de apertura** (email): % de emails abiertos
4. **Tasa de click** (email): % de clicks en acciones
5. **Errores por canal**: Fallos por tipo de canal
6. **Cola de trabajos**: Tamaño y tiempo de espera
7. **Notificaciones por usuario**: Promedio diario/semanal

---

## 🚨 Consideraciones Importantes

### Seguridad
- ✅ Validar permisos antes de enviar notificaciones
- ✅ Sanitizar datos en plantillas para evitar XSS
- ✅ Rate limiting para prevenir spam
- ✅ Encriptar datos sensibles en notificaciones

### Performance
- ✅ Usar colas para todas las notificaciones externas (email, SMS)
- ✅ Implementar batch notifications para envíos masivos
- ✅ Cachear preferencias de usuario
- ✅ Limitar tamaño de payload en broadcast

### UX
- ✅ No saturar al usuario con notificaciones
- ✅ Permitir silenciar notificaciones temporalmente
- ✅ Agrupar notificaciones similares
- ✅ Sonidos opcionales y no intrusivos

### Compliance
- ✅ GDPR: Permitir exportar/eliminar notificaciones
- ✅ CAN-SPAM: Incluir unsubscribe en emails
- ✅ Logs de consentimiento para marketing

---

## 🎓 Recursos de Aprendizaje

### Documentación Oficial
- [Laravel Notifications](https://laravel.com/docs/12.x/notifications)
- [Laravel Broadcasting](https://laravel.com/docs/12.x/broadcasting)
- [Laravel Reverb](https://laravel.com/docs/12.x/reverb)
- [Laravel Queues](https://laravel.com/docs/12.x/queues)

### Packages
- [Spatie Notification Log](https://spatie.be/docs/laravel-notification-log)
- [Laravel Notification Channels](https://laravel-notification-channels.com/)

### Tutoriales
- [Laracasts: Notifications](https://laracasts.com/series/whats-new-in-laravel-11/episodes/4)
- [Laravel Daily: Advanced Notifications](https://laraveldaily.com/)

---

## 💡 Recomendación Final

**Orden de implementación sugerido:**

1. **FASE 1 (Email)** - Fundamental para cualquier app
2. **FASE 2 (Colas)** - Crítico para performance
3. **FASE 3 (Tiempo Real)** - Gran mejora de UX
4. **FASE 4 (Logging)** - Importante para debugging
5. **FASE 5 (Preferencias)** - Mejora la experiencia del usuario
6. **FASE 6 y 7** - Solo si hay necesidad específica

**Tiempo total estimado:** 10-15 horas para FASES 1-5

**Beneficios:**
- ✅ Sistema robusto y escalable
- ✅ Mejor experiencia de usuario
- ✅ Fácil mantenimiento y extensión
- ✅ Preparado para crecimiento futuro
