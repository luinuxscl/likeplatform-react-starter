# Ejemplo de Widget para Packages

Este documento muestra cómo crear widgets personalizados en un package.

## Estructura de Archivos

```
packages/mi-empresa/mi-package/
├── config/
│   └── widgets.php              # Configuración de widgets
├── resources/
│   └── js/
│       └── widgets/
│           └── MiWidget.tsx     # Componente React del widget
└── src/
    └── Http/
        └── Controllers/
            └── WidgetDataController.php  # Endpoint de datos (opcional)
```

## 1. Configuración del Widget

**Archivo**: `packages/mi-empresa/mi-package/config/widgets.php`

```php
<?php

return [
    'widgets' => [
        [
            'key' => 'mi-package-stats',
            'component' => 'MiPackageStatsWidget',
            'title' => 'Package Statistics',
            'description' => 'Real-time statistics from my package',
            'permission' => 'mi-package.view',
            'size' => 'col-span-12 md:col-span-6',
            'order' => 10,
            'active' => true,
            'refresh_interval' => 300, // 5 minutos
            'endpoint' => '/api/mi-package/widget-data',
            'config' => [
                'show_chart' => true,
                'metric' => 'total',
            ],
        ],
        [
            'key' => 'mi-package-alerts',
            'component' => 'MiPackageAlertsWidget',
            'title' => 'Recent Alerts',
            'description' => 'Latest alerts and notifications',
            'permission' => 'mi-package.alerts.view',
            'size' => 'col-span-12 md:col-span-6',
            'order' => 20,
            'active' => true,
        ],
    ],
];
```

## 2. Componente React del Widget

**Archivo**: `packages/mi-empresa/mi-package/resources/js/widgets/MiPackageStatsWidget.tsx`

```tsx
import { useState, useEffect } from 'react';
import { BaseWidget } from '@/components/widgets/BaseWidget';
import type { Widget } from '@/types';

interface StatsData {
    total: number;
    active: number;
    pending: number;
}

export function MiPackageStatsWidget({ 
    widget, 
    onRefresh 
}: { 
    widget: Widget; 
    onRefresh?: () => void 
}) {
    const [data, setData] = useState<StatsData | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchData = async () => {
        try {
            setIsLoading(true);
            setError(null);

            const response = await fetch(widget.endpoint || '/api/mi-package/stats');
            
            if (!response.ok) {
                throw new Error('Failed to fetch data');
            }

            const result = await response.json();
            setData(result.data);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An error occurred');
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchData();

        // Auto-refresh si está configurado
        if (widget.refresh_interval) {
            const interval = setInterval(fetchData, widget.refresh_interval * 1000);
            return () => clearInterval(interval);
        }
    }, [widget.refresh_interval]);

    const handleRefresh = () => {
        fetchData();
        onRefresh?.();
    };

    return (
        <BaseWidget
            title={widget.title}
            description={widget.description}
            isLoading={isLoading}
            error={error}
            isEmpty={!data}
            onRefresh={handleRefresh}
        >
            {data && (
                <div className="grid grid-cols-3 gap-4">
                    <div className="text-center">
                        <p className="text-3xl font-bold">{data.total}</p>
                        <p className="text-sm text-muted-foreground">Total</p>
                    </div>
                    <div className="text-center">
                        <p className="text-3xl font-bold text-green-600">{data.active}</p>
                        <p className="text-sm text-muted-foreground">Active</p>
                    </div>
                    <div className="text-center">
                        <p className="text-3xl font-bold text-yellow-600">{data.pending}</p>
                        <p className="text-sm text-muted-foreground">Pending</p>
                    </div>
                </div>
            )}
        </BaseWidget>
    );
}
```

## 3. Endpoint de Datos (Opcional)

**Archivo**: `packages/mi-empresa/mi-package/src/Http/Controllers/WidgetDataController.php`

```php
<?php

namespace MiEmpresa\MiPackage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WidgetDataController extends Controller
{
    public function stats(): JsonResponse
    {
        // Verificar permisos
        if (!auth()->user()->can('mi-package.view')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = [
            'total' => 150,
            'active' => 120,
            'pending' => 30,
        ];

        return response()->json([
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
```

## 4. Registrar Rutas

**Archivo**: `packages/mi-empresa/mi-package/routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use MiEmpresa\MiPackage\Http\Controllers\WidgetDataController;

Route::middleware(['auth'])->prefix('api/mi-package')->group(function () {
    Route::get('/stats', [WidgetDataController::class, 'stats']);
    Route::get('/widget-data', [WidgetDataController::class, 'stats']);
});
```

## 5. Registrar Widget Component en WidgetDashboard

**Archivo**: `resources/js/components/widgets/WidgetDashboard.tsx`

Agregar el widget al mapa de componentes:

```tsx
// Lazy load de widgets
const MiPackageStatsWidget = lazy(() =>
    import('@/../../packages/mi-empresa/mi-package/resources/js/widgets/MiPackageStatsWidget').then(
        (module) => ({ default: module.MiPackageStatsWidget })
    ),
);

const WIDGET_COMPONENTS: Record<string, React.LazyExoticComponent<WidgetComponent>> = {
    WelcomeWidget: WelcomeWidget as React.LazyExoticComponent<WidgetComponent>,
    MiPackageStatsWidget: MiPackageStatsWidget as React.LazyExoticComponent<WidgetComponent>,
};
```

## 6. Hooks del Package (Opcional)

**Archivo**: `packages/mi-empresa/mi-package/src/Package.php`

```php
<?php

namespace MiEmpresa\MiPackage;

use App\Support\CustomizationPackage;

class Package extends CustomizationPackage
{
    public function getName(): string
    {
        return 'mi-package';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    /**
     * Hook cuando un widget es refrescado
     */
    public function onWidgetRefresh(string $widgetKey, array $data): void
    {
        if ($widgetKey === 'mi-package-stats') {
            // Lógica personalizada al refrescar
            \Log::info('Widget refrescado', [
                'widget' => $widgetKey,
                'user_id' => $data['user_id'] ?? null,
            ]);
        }
    }

    /**
     * Hook cuando la configuración de un widget cambia
     */
    public function onWidgetConfigUpdated(string $widgetKey, array $config): void
    {
        if ($widgetKey === 'mi-package-stats') {
            // Lógica personalizada al cambiar configuración
            \Log::info('Configuración de widget actualizada', [
                'widget' => $widgetKey,
                'config' => $config,
            ]);
        }
    }
}
```

## Propiedades Disponibles

### Configuración del Widget

| Propiedad | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `key` | string | ✅ | Identificador único del widget |
| `component` | string | ✅ | Nombre del componente React |
| `title` | string | ✅ | Título del widget |
| `description` | string | ❌ | Descripción del widget |
| `permission` | string | ❌ | Permiso requerido para ver el widget |
| `size` | string | ✅ | Clases Tailwind para tamaño (ej: `col-span-6`) |
| `order` | int | ❌ | Orden de visualización (default: 999) |
| `active` | bool | ❌ | Si el widget está activo (default: true) |
| `refresh_interval` | int | ❌ | Intervalo de auto-refresh en segundos |
| `endpoint` | string | ❌ | Endpoint para obtener datos |
| `config` | array | ❌ | Configuración adicional del widget |

### Tamaños Predefinidos

```php
'size' => 'col-span-12',                    // Full width
'size' => 'col-span-12 md:col-span-6',      // Half width en desktop
'size' => 'col-span-12 md:col-span-4',      // Third width en desktop
'size' => 'col-span-12 md:col-span-8',      // Two thirds en desktop
```

## Testing

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class WidgetTest extends TestCase
{
    public function test_widget_appears_in_discovery(): void
    {
        $service = app(\App\Services\WidgetService::class);
        $widgets = $service->discover();

        $widget = collect($widgets)->firstWhere('key', 'mi-package-stats');

        $this->assertNotNull($widget);
        $this->assertEquals('MiPackageStatsWidget', $widget['component']);
    }

    public function test_widget_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/mi-package/stats');

        $response->assertStatus(401);
    }

    public function test_widget_endpoint_returns_data(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo('mi-package.view');

        $response = $this->actingAs($user)->getJson('/api/mi-package/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['total', 'active', 'pending'],
                'timestamp',
            ]);
    }
}
```

## Mejores Prácticas

1. **Permisos**: Siempre verificar permisos en el backend
2. **Caché**: Cachear datos costosos en el backend
3. **Loading States**: Usar estados de carga apropiados
4. **Error Handling**: Manejar errores gracefully
5. **Auto-refresh**: Usar intervalos razonables (≥60s)
6. **Responsive**: Usar tamaños responsive con Tailwind
7. **TypeScript**: Definir tipos para los datos del widget
8. **Testing**: Escribir tests para widgets y endpoints

## Recursos

- [BaseWidget Component](../../resources/js/components/widgets/BaseWidget.tsx)
- [Widget Types](../../resources/js/types/widget.d.ts)
- [useWidgets Hook](../../resources/js/hooks/useWidgets.tsx)
- [Sistema de Packages](../sistemas/packages-personalizacion.md)
