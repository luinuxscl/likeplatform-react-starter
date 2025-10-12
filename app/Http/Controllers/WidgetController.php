<?php

namespace App\Http\Controllers;

use App\Models\UserWidget;
use App\Services\Audit\AuditLogger;
use App\Services\WidgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Controlador para gestionar widgets del dashboard
 */
class WidgetController extends Controller
{
    public function __construct(
        private WidgetService $widgetService,
        private AuditLogger $auditLogger
    ) {}

    /**
     * Lista todos los widgets disponibles para el usuario actual
     *
     * GET /api/widgets
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $widgets = $this->widgetService->getForUser($user);

        return response()->json([
            'widgets' => $widgets,
        ]);
    }

    /**
     * Obtiene el layout personalizado del usuario
     *
     * GET /api/widgets/layout
     */
    public function getLayout(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userWidgets = UserWidget::getLayoutForUser($user->id);
        $availableWidgets = $this->widgetService->getForUser($user);

        // Combinar widgets disponibles con configuración del usuario
        $layout = [];
        $userWidgetsMap = $userWidgets->keyBy('widget_key');

        foreach ($availableWidgets as $widget) {
            $userWidget = $userWidgetsMap->get($widget['key']);

            $layout[] = [
                'key' => $widget['key'],
                'component' => $widget['component'],
                'title' => $widget['title'],
                'description' => $widget['description'] ?? null,
                'size' => $userWidget?->size ?? $widget['size'],
                'position' => $userWidget?->position ?? $widget['order'] ?? 999,
                'visible' => $userWidget?->visible ?? true,
                'config' => $userWidget?->config ?? $widget['config'] ?? null,
                'refresh_interval' => $widget['refresh_interval'] ?? null,
                'endpoint' => $widget['endpoint'] ?? null,
                'package' => $widget['package'] ?? 'core',
            ];
        }

        // Ordenar por posición
        usort($layout, fn($a, $b) => $a['position'] <=> $b['position']);

        return response()->json([
            'layout' => $layout,
        ]);
    }

    /**
     * Guarda el layout personalizado del usuario
     *
     * PUT /api/widgets/layout
     */
    public function saveLayout(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.key' => 'required|string',
            'widgets.*.size' => 'required|string',
            'widgets.*.visible' => 'boolean',
            'widgets.*.config' => 'nullable|array',
        ]);

        try {
            UserWidget::saveLayoutForUser($user->id, $validated['widgets']);

            // Auditar cambio
            $this->auditLogger->log(
                action: 'widget_layout_updated',
                auditable: $user,
                metadata: [
                    'widgets_count' => count($validated['widgets']),
                ]
            );

            return response()->json([
                'message' => 'Layout saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to save layout',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resetea el layout del usuario a los valores por defecto
     *
     * POST /api/widgets/layout/reset
     */
    public function resetLayout(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            UserWidget::resetLayoutForUser($user->id);

            // Auditar cambio
            $this->auditLogger->log(
                action: 'widget_layout_reset',
                auditable: $user
            );

            return response()->json([
                'message' => 'Layout reset successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to reset layout',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle de visibilidad de un widget
     *
     * POST /api/widgets/{key}/toggle
     */
    public function toggleVisibility(string $key): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verificar que el widget existe
        $widget = $this->widgetService->getWidget($key);

        if (!$widget) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        try {
            $visible = UserWidget::toggleVisibility($user->id, $key);

            // Auditar cambio
            $this->auditLogger->log(
                action: 'widget_visibility_toggled',
                auditable: $user,
                metadata: [
                    'widget_key' => $key,
                    'visible' => $visible,
                ]
            );

            return response()->json([
                'message' => 'Widget visibility toggled',
                'visible' => $visible,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to toggle visibility',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresca los datos de un widget específico
     *
     * POST /api/widgets/{key}/refresh
     */
    public function refresh(string $key): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verificar que el widget existe
        $widget = $this->widgetService->getWidget($key);

        if (!$widget) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        // Verificar permisos
        if (isset($widget['permission']) && !$user->can($widget['permission'])) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        try {
            // Notificar al package sobre el refresh
            $this->widgetService->notifyWidgetRefresh($key, [
                'user_id' => $user->id,
                'timestamp' => now()->toIso8601String(),
            ]);

            // Auditar refresh
            $this->auditLogger->log(
                action: 'widget_refreshed',
                auditable: $user,
                metadata: [
                    'widget_key' => $key,
                ]
            );

            return response()->json([
                'message' => 'Widget refreshed successfully',
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to refresh widget',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza la configuración de un widget
     *
     * PUT /api/widgets/{key}/config
     */
    public function updateConfig(Request $request, string $key): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verificar que el widget existe
        $widget = $this->widgetService->getWidget($key);

        if (!$widget) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        $validated = $request->validate([
            'config' => 'required|array',
        ]);

        try {
            $userWidget = UserWidget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'widget_key' => $key,
                ],
                [
                    'config' => $validated['config'],
                ]
            );

            // Notificar al package sobre el cambio de configuración
            $this->widgetService->notifyWidgetConfigUpdated($key, $validated['config']);

            // Auditar cambio
            $this->auditLogger->log(
                action: 'widget_config_updated',
                auditable: $user,
                metadata: [
                    'widget_key' => $key,
                    'config' => $validated['config'],
                ]
            );

            return response()->json([
                'message' => 'Widget configuration updated',
                'config' => $userWidget->config,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Limpia la caché de widgets
     *
     * POST /api/widgets/cache/clear
     */
    public function clearCache(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verificar permisos de administrador
        if (!$user->hasRole('admin')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        try {
            $this->widgetService->clearCache();

            // Auditar acción
            $this->auditLogger->log(
                action: 'widget_cache_cleared',
                auditable: $user
            );

            return response()->json([
                'message' => 'Widget cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to clear cache',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
