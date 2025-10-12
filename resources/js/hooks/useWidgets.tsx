import { useState, useEffect, useCallback } from 'react';
import { router } from '@inertiajs/react';
import type {
    Widget,
    WidgetConfig,
    UseWidgetsReturn,
    WidgetLayoutApiResponse,
    WidgetToggleResponse,
    WidgetConfigUpdateResponse,
} from '@/types';

/**
 * Hook para gestionar widgets del dashboard
 */
export function useWidgets(): UseWidgetsReturn {
    const [widgets, setWidgets] = useState<Widget[]>([]);
    const [layout, setLayout] = useState<Widget[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    /**
     * Carga el layout de widgets del usuario
     */
    const fetchLayout = useCallback(async () => {
        try {
            setIsLoading(true);
            setError(null);

            const response = await fetch('/api/widgets/layout');

            if (!response.ok) {
                throw new Error('Failed to fetch widgets');
            }

            const data: WidgetLayoutApiResponse = await response.json();
            setLayout(data.layout);
            setWidgets(data.layout.filter((w) => w.visible));
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An error occurred');
            console.error('Error fetching widgets:', err);
        } finally {
            setIsLoading(false);
        }
    }, []);

    /**
     * Refresca un widget específico
     */
    const refreshWidget = useCallback(async (key: string) => {
        try {
            const response = await fetch(`/api/widgets/${key}/refresh`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                            ?.content || '',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to refresh widget');
            }

            // Opcional: Recargar layout completo o actualizar solo el widget
            await fetchLayout();
        } catch (err) {
            console.error('Error refreshing widget:', err);
            throw err;
        }
    }, [fetchLayout]);

    /**
     * Toggle de visibilidad de un widget
     */
    const toggleVisibility = useCallback(async (key: string) => {
        try {
            const response = await fetch(`/api/widgets/${key}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                            ?.content || '',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to toggle widget visibility');
            }

            const data: WidgetToggleResponse = await response.json();

            // Actualizar estado local
            setLayout((prev) =>
                prev.map((w) => (w.key === key ? { ...w, visible: data.visible } : w)),
            );
            setWidgets((prev) =>
                data.visible
                    ? [...prev, layout.find((w) => w.key === key)!]
                    : prev.filter((w) => w.key !== key),
            );
        } catch (err) {
            console.error('Error toggling widget visibility:', err);
            throw err;
        }
    }, [layout]);

    /**
     * Guarda el layout completo
     */
    const saveLayout = useCallback(async (newWidgets: Widget[]) => {
        try {
            const response = await fetch('/api/widgets/layout', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                            ?.content || '',
                },
                body: JSON.stringify({
                    widgets: newWidgets.map((w, index) => ({
                        key: w.key,
                        size: w.size,
                        visible: w.visible,
                        config: w.config,
                        position: index,
                    })),
                }),
            });

            if (!response.ok) {
                throw new Error('Failed to save layout');
            }

            // Actualizar estado local
            setLayout(newWidgets);
            setWidgets(newWidgets.filter((w) => w.visible));

            // Opcional: Mostrar notificación de éxito
            router.reload({ only: [] }); // Trigger Inertia reload sin cambiar página
        } catch (err) {
            console.error('Error saving layout:', err);
            throw err;
        }
    }, []);

    /**
     * Resetea el layout a valores por defecto
     */
    const resetLayout = useCallback(async () => {
        try {
            const response = await fetch('/api/widgets/layout/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                            ?.content || '',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to reset layout');
            }

            // Recargar layout
            await fetchLayout();
        } catch (err) {
            console.error('Error resetting layout:', err);
            throw err;
        }
    }, [fetchLayout]);

    /**
     * Actualiza la configuración de un widget
     */
    const updateWidgetConfig = useCallback(async (key: string, config: WidgetConfig) => {
        try {
            const response = await fetch(`/api/widgets/${key}/config`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                            ?.content || '',
                },
                body: JSON.stringify({ config }),
            });

            if (!response.ok) {
                throw new Error('Failed to update widget configuration');
            }

            const data: WidgetConfigUpdateResponse = await response.json();

            // Actualizar estado local
            setLayout((prev) =>
                prev.map((w) => (w.key === key ? { ...w, config: data.config } : w)),
            );
            setWidgets((prev) =>
                prev.map((w) => (w.key === key ? { ...w, config: data.config } : w)),
            );
        } catch (err) {
            console.error('Error updating widget config:', err);
            throw err;
        }
    }, []);

    // Cargar layout al montar el componente
    useEffect(() => {
        fetchLayout();
    }, [fetchLayout]);

    return {
        widgets,
        layout,
        isLoading,
        error,
        refreshWidget,
        toggleVisibility,
        saveLayout,
        resetLayout,
        updateWidgetConfig,
    };
}
