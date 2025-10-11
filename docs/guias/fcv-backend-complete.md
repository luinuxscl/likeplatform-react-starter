# Backend FCV - Guía Completa

Esta guía documenta la implementación completa del backend del package FCV, incluyendo integración con el sistema de auditoría, analytics y excepciones de acceso.

---

## 📋 Índice

1. [Sistema de Auditoría](#sistema-de-auditoría)
2. [Sistema de Analytics](#sistema-de-analytics)
3. [Sistema de Excepciones](#sistema-de-excepciones)
4. [API Reference](#api-reference)
5. [Testing](#testing)

---

## 🔍 Sistema de Auditoría

### Integración con AuditLog

FCV está completamente integrado con el sistema de auditoría del starter kit. Todos los cambios en modelos y acciones críticas se registran automáticamente.

### Modelos Auditados

Los siguientes modelos FCV tienen auditoría automática mediante el trait `HasAuditLogs`:

```php
// Person, Organization, Course, Membership, AccessException
use App\Traits\HasAuditLogs;

class Person extends Model
{
    use HasFactory, HasAuditLogs;
}
```

**Acciones auditadas automáticamente:**
- `created` - Creación de registro
- `updated` - Actualización de registro
- `deleted` - Eliminación de registro
- `restored` - Restauración de soft delete

### Acciones Personalizadas

Además de la auditoría automática, FCV registra acciones específicas:

#### Verificación de Acceso
```php
// Acción: fcv.access.verification
// Metadata: rut, allowed, status, reason, organization, course
```

#### Entrada
```php
// Acción: fcv.access.entry
// Metadata: access_log_id, direction, status, reason, gatekeeper_id
```

#### Salida
```php
// Acción: fcv.access.exit
// Metadata: access_log_id, direction, status, reason, gatekeeper_id
```

### Consultar Auditoría

**Desde la UI:**
```
/admin/audit/logs
```
Filtrar por acción: `fcv.*`

**Desde código:**
```php
use App\Models\AuditLog;

// Todas las verificaciones
$verifications = AuditLog::query()
    ->where('action', 'fcv.access.verification')
    ->whereBetween('created_at', [$from, $to])
    ->get();

// Cambios en una persona específica
$personChanges = AuditLog::query()
    ->where('auditable_type', Person::class)
    ->where('auditable_id', $personId)
    ->orderByDesc('created_at')
    ->get();
```

---

## 📊 Sistema de Analytics

### Arquitectura

```
AnalyticsService (Genérico)
    ↓ basado en AuditLog
    ↓
FcvAnalyticsService (Específico)
    ↓ extiende AnalyticsService
    ↓ usa AccessLog para datos específicos
    ↓
FcvAnalyticsController
    ↓ expone 12 endpoints REST
```

### AnalyticsService (Genérico)

**Ubicación:** `app/Services/Analytics/AnalyticsService.php`

Este servicio es **reutilizable por cualquier package**.

**Métodos principales:**

```php
use App\Services\Analytics\AnalyticsService;

$analytics = app(AnalyticsService::class);

// Estadísticas generales
$stats = $analytics->getStats('mi-package.action', $from, $to);

// Tendencias
$trends = $analytics->getTrends('mi-package.action', $from, $to, 'day');

// Top usuarios
$topUsers = $analytics->getTopUsers('mi-package.action', $from, $to, 10);

// Distribución por metadata
$distribution = $analytics->getMetadataDistribution(
    'mi-package.action',
    'metadata_key',
    $from,
    $to
);

// Reportes
$dailyReport = $analytics->getDailyReport('mi-package.action', $date);
$weeklyReport = $analytics->getWeeklyReport('mi-package.action', $weekStart);
$monthlyReport = $analytics->getMonthlyReport('mi-package.action', $year, $month);

// Comparar períodos
$comparison = $analytics->comparePeriods(
    'mi-package.action',
    $period1From,
    $period1To,
    $period2From,
    $period2To
);
```

### FcvAnalyticsService (Específico)

**Ubicación:** `packages/fcv/src/Services/FcvAnalyticsService.php`

Métodos específicos para FCV:

```php
use Like\Fcv\Services\FcvAnalyticsService;

$fcvAnalytics = app(FcvAnalyticsService::class);

// Estadísticas de verificaciones
$verificationStats = $fcvAnalytics->getVerificationStats($from, $to);

// Estadísticas de accesos (entradas/salidas)
$accessStats = $fcvAnalytics->getAccessStats($from, $to);

// Tendencias de verificaciones
$trends = $fcvAnalytics->getVerificationTrends($from, $to, 'day');

// Razones de denegación más comunes
$deniedReasons = $fcvAnalytics->getDeniedReasons($from, $to, 10);

// Estadísticas de AccessLog
$accessLogStats = $fcvAnalytics->getAccessLogStats($from, $to);

// Personas con más accesos
$topPersons = $fcvAnalytics->getTopAccessedPersons($from, $to, 10);

// Historial de una persona
$history = $fcvAnalytics->getPersonAccessHistory($personId, 50);

// Distribución por hora del día
$hourlyDist = $fcvAnalytics->getAccessDistributionByHour($from, $to);

// Distribución por día de la semana
$weekdayDist = $fcvAnalytics->getAccessDistributionByWeekday($from, $to);

// Reporte completo
$completeReport = $fcvAnalytics->getCompleteReport($from, $to);
```

### Endpoints REST

**Base URL:** `/fcv/analytics`

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/stats` | Estadísticas generales |
| GET | `/trends` | Tendencias por período |
| GET | `/top-persons` | Top personas con más accesos |
| GET | `/denied-reasons` | Razones de denegación |
| GET | `/person/{id}/history` | Historial de persona |
| GET | `/hourly-distribution` | Distribución por hora |
| GET | `/weekday-distribution` | Distribución por día |
| GET | `/complete-report` | Reporte completo |
| GET | `/daily-report` | Reporte diario |
| GET | `/weekly-report` | Reporte semanal |
| GET | `/monthly-report` | Reporte mensual |
| GET | `/compare-periods` | Comparar períodos |

**Ejemplo de uso:**

```javascript
// Desde frontend
const response = await fetch('/fcv/analytics/stats?from=2025-01-01&to=2025-01-31');
const stats = await response.json();

console.log(stats.access_log_stats);
console.log(stats.verification_stats);
```

---

## 🚨 Sistema de Excepciones

### Concepto

Las excepciones de acceso permiten otorgar acceso temporal a personas que normalmente no lo tendrían, para casos especiales como:

- **Medical:** Citas médicas
- **Special Event:** Eventos especiales
- **Maintenance:** Mantenimiento
- **Administrative:** Casos administrativos
- **Other:** Otros casos

### Modelo AccessException

**Ubicación:** `packages/fcv/src/Models/AccessException.php`

**Campos:**
- `person_id` - Persona con excepción
- `reason` - Razón (enum)
- `description` - Descripción detallada
- `valid_from` - Inicio de validez
- `valid_until` - Fin de validez
- `created_by` - Usuario creador
- `approved_by` - Usuario aprobador
- `status` - Estado (pending, approved, rejected)
- `rejection_reason` - Razón de rechazo

**Estados:**
- `pending` - Pendiente de aprobación
- `approved` - Aprobada
- `rejected` - Rechazada

### Workflow

```
1. Crear excepción (status: pending)
   ↓
2. Aprobar/Rechazar (requiere permisos)
   ↓
3. Si aprobada → Sistema verifica automáticamente
   ↓
4. Acceso permitido si excepción activa
```

### Uso desde Código

```php
use Like\Fcv\Models\AccessException;

// Crear excepción
$exception = AccessException::create([
    'person_id' => $person->id,
    'reason' => 'medical',
    'description' => 'Cita médica urgente',
    'valid_from' => now(),
    'valid_until' => now()->addDays(2),
    'created_by' => auth()->id(),
    'status' => 'pending',
]);

// Aprobar
$exception->approve($user);

// Rechazar
$exception->reject($user, 'No cumple requisitos');

// Verificar si está activa
if ($exception->isActive()) {
    // Excepción válida
}

// Obtener excepciones activas de una persona
$activeExceptions = AccessException::query()
    ->forPerson($personId)
    ->active()
    ->get();
```

### Scopes Útiles

```php
// Excepciones activas
AccessException::active()->get();

// Excepciones pendientes
AccessException::pending()->get();

// Excepciones aprobadas
AccessException::approved()->get();

// Excepciones de una persona
AccessException::forPerson($personId)->get();

// Válidas en una fecha específica
AccessException::validAt($dateTime)->get();
```

### Endpoints REST

**Base URL:** `/fcv/access-exceptions`

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/` | Lista con filtros |
| POST | `/` | Crear excepción |
| GET | `/{id}` | Ver detalle |
| PUT | `/{id}` | Actualizar (solo pending) |
| DELETE | `/{id}` | Eliminar (solo pending) |
| POST | `/{id}/approve` | Aprobar |
| POST | `/{id}/reject` | Rechazar |
| GET | `/person/{id}/active` | Excepciones activas |

**Ejemplos:**

```bash
# Crear excepción
curl -X POST /fcv/access-exceptions \
  -H "Content-Type: application/json" \
  -d '{
    "person_id": 123,
    "reason": "medical",
    "description": "Cita médica",
    "valid_from": "2025-01-15 08:00:00",
    "valid_until": "2025-01-15 18:00:00"
  }'

# Aprobar excepción
curl -X POST /fcv/access-exceptions/1/approve

# Rechazar excepción
curl -X POST /fcv/access-exceptions/1/reject \
  -H "Content-Type: application/json" \
  -d '{"rejection_reason": "No cumple requisitos"}'

# Listar excepciones activas
curl /fcv/access-exceptions?active_only=true

# Excepciones de una persona
curl /fcv/access-exceptions/person/123/active
```

### Integración con Verificación

El sistema verifica automáticamente excepciones activas **antes** de aplicar reglas normales:

```php
// En AccessRuleService::check()
1. Verificar persona existe
2. ✅ Verificar excepción activa (PRIORIDAD)
3. Verificar membresías
4. Verificar cursos y horarios
```

Si existe una excepción activa, el acceso se permite inmediatamente:

```json
{
  "allowed": true,
  "status": "permitido",
  "reason": "Excepción activa: Médico",
  "person": {...},
  "exception": {
    "id": 1,
    "reason": "medical",
    "reason_label": "Médico",
    "description": "Cita médica urgente",
    "valid_from": "2025-01-15T08:00:00",
    "valid_until": "2025-01-15T18:00:00"
  }
}
```

---

## 📚 API Reference

### AccessLog Model - Scopes

```php
use Like\Fcv\Models\AccessLog;

// Filtrar por estado
AccessLog::allowed()->get();
AccessLog::denied()->get();

// Filtrar por dirección
AccessLog::entry()->get();
AccessLog::exit()->get();

// Filtrar por rango de fechas
AccessLog::between($from, $to)->get();

// Filtrar por persona
AccessLog::byPerson($personId)->get();

// Combinar scopes
AccessLog::allowed()
    ->entry()
    ->between($from, $to)
    ->get();
```

### AccessLog Model - Helpers

```php
$log = AccessLog::find(1);

// Verificar estado
$log->isAllowed(); // bool
$log->isDenied(); // bool

// Verificar dirección
$log->isEntry(); // bool
$log->isExit(); // bool
```

---

## 🧪 Testing

### Tests Implementados

**Total:** 22 tests passing (82 assertions)

#### AuditIntegrationTest (6 tests)
```bash
vendor/bin/pest tests/Feature/FCV/AuditIntegrationTest.php
```

- ✓ Registra cambios en Person
- ✓ Registra actualizaciones en Organization
- ✓ Registra verificaciones de acceso
- ✓ Registra entradas
- ✓ Registra salidas
- ✓ Registra eliminación de Course

#### AccessExceptionTest (11 tests)
```bash
vendor/bin/pest tests/Feature/FCV/AccessExceptionTest.php
```

- ✓ Crea una excepción de acceso
- ✓ Lista excepciones con filtros
- ✓ Aprueba una excepción pendiente
- ✓ Rechaza una excepción pendiente
- ✓ No permite aprobar excepción ya aprobada
- ✓ No permite editar excepción aprobada
- ✓ Verifica excepción activa en verificación
- ✓ Ignora excepción vencida
- ✓ Ignora excepción pendiente
- ✓ Obtiene excepciones activas de persona
- ✓ Scope active filtra correctamente

#### Ejecutar todos los tests
```bash
vendor/bin/pest tests/Feature/FCV/
```

### Factories Disponibles

```php
use Like\Fcv\Models\AccessException;
use Like\Fcv\Models\Person;
use Like\Fcv\Models\Organization;
use Like\Fcv\Models\Course;

// AccessException
AccessException::factory()->create();
AccessException::factory()->approved()->create();
AccessException::factory()->rejected()->create();
AccessException::factory()->active()->create();
AccessException::factory()->expired()->create();

// Person
Person::factory()->create();

// Organization
Organization::factory()->create();

// Course
Course::factory()->create();
```

---

## 🎯 Best Practices

### 1. Usar AnalyticsService para Nuevos Packages

Si estás creando un nuevo package y necesitas analytics:

```php
// En tu PackageAnalyticsService
use App\Services\Analytics\AnalyticsService;

class MiPackageAnalyticsService
{
    public function __construct(protected AnalyticsService $analytics) {}
    
    public function getStats(Carbon $from, Carbon $to): array
    {
        return $this->analytics->getStats('mi-package.action', $from, $to);
    }
}
```

### 2. Registrar Acciones en AuditLog

Para acciones importantes de tu package:

```php
use App\Services\Audit\AuditLogger;

app(AuditLogger::class)->log(
    action: 'mi-package.accion-importante',
    model: $model,
    metadata: [
        'key' => 'value',
        // ...
    ]
);
```

### 3. Usar HasAuditLogs en Modelos

Para auditoría automática:

```php
use App\Traits\HasAuditLogs;

class MiModelo extends Model
{
    use HasFactory, HasAuditLogs;
}
```

### 4. Crear Excepciones con Validación

```php
// Validar fechas
if ($validUntil <= $validFrom) {
    throw new \InvalidArgumentException('valid_until debe ser después de valid_from');
}

// Validar razón
$validReasons = ['medical', 'special_event', 'maintenance', 'administrative', 'other'];
if (!in_array($reason, $validReasons)) {
    throw new \InvalidArgumentException('Razón inválida');
}
```

---

## 🔗 Referencias

- [Sistema de Auditoría del Starter Kit](../sistemas/auditoria.md)
- [AI Agent Reference](../desarrollo/ai-agent-reference.md)
- [Package Theme Installation](./package-theme-installation.md)

---

## 📝 Notas Importantes

1. **Auditoría Global:** Todas las acciones FCV son visibles en `/admin/audit/logs`
2. **Analytics Reutilizable:** `AnalyticsService` puede ser usado por cualquier package
3. **Excepciones Requieren Aprobación:** Solo excepciones `approved` son consideradas
4. **Scopes Optimizados:** Usa scopes para queries eficientes
5. **Tests Completos:** 95.6% de tests passing

---

**Última actualización:** 2025-10-11  
**Versión:** 1.0.0
