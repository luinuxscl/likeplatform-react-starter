import { lazy, Suspense, ComponentType } from 'react';
import { RefreshCw, RotateCcw } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Skeleton } from '@/components/ui/skeleton';
import { useWidgets } from '@/hooks/useWidgets';
import type { WidgetDashboardProps, Widget } from '@/types';
import { cn } from '@/lib/utils';

// Lazy load de widgets
const WelcomeWidget = lazy(() =>
    import('./WelcomeWidget').then((module) => ({ default: module.WelcomeWidget })),
);

const StatsWidget = lazy(() =>
    import('./StatsWidget').then((module) => ({ default: module.StatsWidget })),
);

const ActivityWidget = lazy(() =>
    import('./ActivityWidget').then((module) => ({ default: module.ActivityWidget })),
);

const GoalsWidget = lazy(() =>
    import('./GoalsWidget').then((module) => ({ default: module.GoalsWidget })),
);

const QuickActionsWidget = lazy(() =>
    import('./QuickActionsWidget').then((module) => ({ default: module.QuickActionsWidget })),
);

/**
 * Tipo para componentes de widgets
 */
type WidgetComponent = ComponentType<{ widget: Widget; onRefresh?: () => void }>;

/**
 * Mapa de componentes de widgets disponibles
 */
const WIDGET_COMPONENTS: Record<string, React.LazyExoticComponent<WidgetComponent>> = {
    WelcomeWidget: WelcomeWidget as React.LazyExoticComponent<WidgetComponent>,
    StatsWidget: StatsWidget as React.LazyExoticComponent<WidgetComponent>,
    ActivityWidget: ActivityWidget as React.LazyExoticComponent<WidgetComponent>,
    GoalsWidget: GoalsWidget as React.LazyExoticComponent<WidgetComponent>,
    QuickActionsWidget: QuickActionsWidget as React.LazyExoticComponent<WidgetComponent>,
};

/**
 * Dashboard de widgets modular y configurable
 */
export function WidgetDashboard({
    layout = 'grid',
    columns = 12,
    allowCustomization = true,
    className,
}: WidgetDashboardProps) {
    const { widgets, isLoading, error, refreshWidget, resetLayout } = useWidgets();

    if (isLoading) {
        return <DashboardSkeleton columns={columns} />;
    }

    if (error) {
        return (
            <Alert variant="destructive">
                <AlertDescription>{error}</AlertDescription>
            </Alert>
        );
    }

    if (widgets.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-12 text-center">
                <p className="text-muted-foreground mb-4">No widgets available</p>
                {allowCustomization && (
                    <Button variant="outline" onClick={resetLayout}>
                        <RotateCcw className="mr-2 h-4 w-4" />
                        Reset to default layout
                    </Button>
                )}
            </div>
        );
    }

    return (
        <div className={cn('space-y-6', className)}>
            {/* Toolbar */}
            {allowCustomization && (
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Dashboard</h2>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => {
                                widgets.forEach((widget) => refreshWidget(widget.key));
                            }}
                        >
                            <RefreshCw className="mr-2 h-4 w-4" />
                            Refresh All
                        </Button>
                        <Button variant="outline" size="sm" onClick={resetLayout}>
                            <RotateCcw className="mr-2 h-4 w-4" />
                            Reset Layout
                        </Button>
                    </div>
                </div>
            )}

            {/* Grid de widgets */}
            <div
                className={cn(
                    'grid gap-6',
                    layout === 'grid' && `grid-cols-${columns}`,
                    layout === 'masonry' && 'masonry',
                )}
            >
                {widgets.map((widget) => (
                    <WidgetRenderer
                        key={widget.key}
                        widget={widget}
                        onRefresh={() => refreshWidget(widget.key)}
                    />
                ))}
            </div>
        </div>
    );
}

/**
 * Renderiza un widget individual
 */
function WidgetRenderer({
    widget,
    onRefresh,
}: {
    widget: Widget;
    onRefresh: () => void;
}) {
    const WidgetComponent = WIDGET_COMPONENTS[widget.component];

    if (!WidgetComponent) {
        return (
            <div className={cn('rounded-lg border border-dashed p-4', widget.size)}>
                <p className="text-sm text-muted-foreground">
                    Widget component "{widget.component}" not found
                </p>
            </div>
        );
    }

    return (
        <div className={cn(widget.size)}>
            <Suspense fallback={<WidgetSkeleton />}>
                <WidgetComponent widget={widget} onRefresh={onRefresh} />
            </Suspense>
        </div>
    );
}

/**
 * Skeleton para widgets individuales
 */
function WidgetSkeleton() {
    return (
        <div className="rounded-lg border p-6 space-y-4">
            <div className="flex items-center justify-between">
                <Skeleton className="h-6 w-32" />
                <Skeleton className="h-8 w-8 rounded-full" />
            </div>
            <Skeleton className="h-4 w-full" />
            <Skeleton className="h-4 w-3/4" />
            <Skeleton className="h-32 w-full mt-4" />
        </div>
    );
}

/**
 * Skeleton para el dashboard completo
 */
function DashboardSkeleton({ columns }: { columns: number }) {
    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <Skeleton className="h-8 w-32" />
                <div className="flex gap-2">
                    <Skeleton className="h-9 w-28" />
                    <Skeleton className="h-9 w-28" />
                </div>
            </div>
            <div className={`grid grid-cols-${columns} gap-6`}>
                <div className="col-span-12">
                    <WidgetSkeleton />
                </div>
                <div className="col-span-12 md:col-span-6">
                    <WidgetSkeleton />
                </div>
                <div className="col-span-12 md:col-span-6">
                    <WidgetSkeleton />
                </div>
            </div>
        </div>
    );
}
