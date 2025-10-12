import { RefreshCw, AlertCircle, Inbox } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Skeleton } from '@/components/ui/skeleton';
import { useI18n } from '@/lib/i18n/I18nProvider';
import type { BaseWidgetProps } from '@/types';

/**
 * Componente base para widgets del dashboard
 * Maneja estados: loading, error, empty, success
 */
export function BaseWidget({
    title,
    description,
    isLoading = false,
    error = null,
    isEmpty = false,
    onRefresh,
    refreshInterval,
    children,
    actions,
}: BaseWidgetProps) {
    const { t } = useI18n();
    return (
        <Card className="h-full flex flex-col">
            <CardHeader className="flex-none">
                <div className="flex items-start justify-between">
                    <div className="space-y-1">
                        <CardTitle className="text-lg">{title}</CardTitle>
                        {description && (
                            <CardDescription className="text-sm">{description}</CardDescription>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        {actions}
                        {onRefresh && (
                            <Button
                                variant="ghost"
                                size="icon"
                                onClick={onRefresh}
                                disabled={isLoading}
                                title={t('Refresh widget')}
                            >
                                <RefreshCw
                                    className={`h-4 w-4 ${isLoading ? 'animate-spin' : ''}`}
                                />
                            </Button>
                        )}
                    </div>
                </div>
            </CardHeader>

            <CardContent className="flex-1 flex flex-col">
                {isLoading ? (
                    <WidgetSkeleton />
                ) : error ? (
                    <WidgetError error={error} onRetry={onRefresh} />
                ) : isEmpty ? (
                    <WidgetEmpty />
                ) : (
                    <div className="flex-1">{children}</div>
                )}
            </CardContent>
        </Card>
    );
}

/**
 * Skeleton loading state
 */
function WidgetSkeleton() {
    return (
        <div className="space-y-3">
            <Skeleton className="h-4 w-full" />
            <Skeleton className="h-4 w-3/4" />
            <Skeleton className="h-4 w-5/6" />
            <Skeleton className="h-32 w-full mt-4" />
        </div>
    );
}

/**
 * Error state
 */
function WidgetError({ error, onRetry }: { error: string; onRetry?: () => void }) {
    const { t } = useI18n();
    return (
        <Alert variant="destructive">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription className="flex items-center justify-between">
                <span>{error}</span>
                {onRetry && (
                    <Button variant="outline" size="sm" onClick={onRetry}>
                        {t('Retry')}
                    </Button>
                )}
            </AlertDescription>
        </Alert>
    );
}

/**
 * Empty state
 */
function WidgetEmpty() {
    const { t } = useI18n();
    return (
        <div className="flex flex-col items-center justify-center h-full text-muted-foreground py-8">
            <Inbox className="h-12 w-12 mb-4 opacity-50" />
            <p className="text-sm">{t('No data available')}</p>
        </div>
    );
}
