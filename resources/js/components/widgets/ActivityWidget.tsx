import { useState, useEffect } from 'react';
import { BaseWidget } from './BaseWidget';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';
import { Button } from '@/components/ui/button';
import { useI18n } from '@/lib/i18n/I18nProvider';
import type { Widget } from '@/types';

interface ActivityData {
    date: string;
    value: number;
    comparison: number;
}

/**
 * Widget de actividad con gráfico de líneas suave
 */
export function ActivityWidget({ widget, onRefresh }: { widget: Widget; onRefresh?: () => void }) {
    const { t } = useI18n();
    const [data, setData] = useState<ActivityData[] | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [period, setPeriod] = useState<'week' | 'month'>('week');

    useEffect(() => {
        loadData();
    }, [period]);

    const loadData = () => {
        setIsLoading(true);
        // Simular carga de datos (reemplazar con API real)
        setTimeout(() => {
            const weekData: ActivityData[] = [
                { date: 'Mon', value: 420, comparison: 380 },
                { date: 'Tue', value: 380, comparison: 420 },
                { date: 'Wed', value: 520, comparison: 450 },
                { date: 'Thu', value: 680, comparison: 520 },
                { date: 'Fri', value: 580, comparison: 600 },
                { date: 'Sat', value: 720, comparison: 680 },
                { date: 'Sun', value: 850, comparison: 720 },
            ];

            const monthData: ActivityData[] = [
                { date: 'Week 1', value: 2400, comparison: 2200 },
                { date: 'Week 2', value: 2800, comparison: 2400 },
                { date: 'Week 3', value: 3200, comparison: 2800 },
                { date: 'Week 4', value: 3800, comparison: 3400 },
            ];

            setData(period === 'week' ? weekData : monthData);
            setIsLoading(false);
        }, 500);
    };

    const handleRefresh = () => {
        loadData();
        onRefresh?.();
    };

    // Calcular totales
    const currentTotal = data?.reduce((sum, item) => sum + item.value, 0) || 0;
    const previousTotal = data?.reduce((sum, item) => sum + item.comparison, 0) || 0;
    const change = previousTotal > 0 ? ((currentTotal - previousTotal) / previousTotal) * 100 : 0;

    return (
        <BaseWidget
            title={widget.title}
            description={widget.description}
            isLoading={isLoading}
            isEmpty={!data}
            onRefresh={handleRefresh}
            actions={
                <div className="flex gap-2">
                    <Button
                        variant={period === 'week' ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setPeriod('week')}
                    >
                        {t('Week')}
                    </Button>
                    <Button
                        variant={period === 'month' ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setPeriod('month')}
                    >
                        {t('Month')}
                    </Button>
                </div>
            }
        >
            {data && (
                <div className="space-y-4">
                    {/* Stats Header */}
                    <div className="flex items-end justify-between">
                        <div>
                            <div className="text-3xl font-bold">
                                {currentTotal.toLocaleString()}
                            </div>
                            <div className="text-sm text-muted-foreground mt-1">
                                Total activity this {period}
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="text-center">
                                <div className="text-2xl font-bold">{change > 0 ? '+' : ''}{change.toFixed(1)}%</div>
                                <div className="text-xs text-muted-foreground">{t('Change')}</div>
                            </div>
                        </div>
                    </div>

                    {/* Chart */}
                    <div className="h-64 w-full">
                        <ResponsiveContainer width="100%" height="100%">
                            <AreaChart
                                data={data}
                                margin={{ top: 10, right: 10, left: 0, bottom: 0 }}
                            >
                                <defs>
                                    <linearGradient id="colorValue" x1="0" y1="0" x2="0" y2="1">
                                        <stop
                                            offset="5%"
                                            stopColor="hsl(var(--primary))"
                                            stopOpacity={0.3}
                                        />
                                        <stop
                                            offset="95%"
                                            stopColor="hsl(var(--primary))"
                                            stopOpacity={0}
                                        />
                                    </linearGradient>
                                    <linearGradient id="colorComparison" x1="0" y1="0" x2="0" y2="1">
                                        <stop
                                            offset="5%"
                                            stopColor="hsl(var(--muted-foreground))"
                                            stopOpacity={0.2}
                                        />
                                        <stop
                                            offset="95%"
                                            stopColor="hsl(var(--muted-foreground))"
                                            stopOpacity={0}
                                        />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid
                                    strokeDasharray="3 3"
                                    stroke="hsl(var(--border))"
                                    opacity={0.3}
                                />
                                <XAxis
                                    dataKey="date"
                                    stroke="hsl(var(--muted-foreground))"
                                    fontSize={12}
                                    tickLine={false}
                                    axisLine={false}
                                />
                                <YAxis
                                    stroke="hsl(var(--muted-foreground))"
                                    fontSize={12}
                                    tickLine={false}
                                    axisLine={false}
                                    tickFormatter={(value) => `${value}`}
                                />
                                <Tooltip
                                    contentStyle={{
                                        backgroundColor: 'hsl(var(--popover))',
                                        border: '1px solid hsl(var(--border))',
                                        borderRadius: '8px',
                                        padding: '8px 12px',
                                    }}
                                    labelStyle={{
                                        color: 'hsl(var(--popover-foreground))',
                                        fontWeight: 600,
                                        marginBottom: '4px',
                                    }}
                                />
                                <Area
                                    type="monotone"
                                    dataKey="comparison"
                                    stroke="hsl(var(--muted-foreground))"
                                    strokeWidth={2}
                                    fillOpacity={1}
                                    fill="url(#colorComparison)"
                                    strokeDasharray="5 5"
                                />
                                <Area
                                    type="monotone"
                                    dataKey="value"
                                    stroke="hsl(var(--primary))"
                                    strokeWidth={3}
                                    fillOpacity={1}
                                    fill="url(#colorValue)"
                                />
                            </AreaChart>
                        </ResponsiveContainer>
                    </div>

                    {/* Legend */}
                    <div className="flex items-center justify-center gap-6 text-sm">
                        <div className="flex items-center gap-2">
                            <div className="h-3 w-3 rounded-full bg-primary" />
                            <span className="text-sm text-muted-foreground">{t('Current')}</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="h-3 w-3 rounded-full bg-muted-foreground/30" />
                            <span className="text-sm text-muted-foreground">{t('Previous')}</span>
                        </div>
                    </div>
                </div>
            )}
        </BaseWidget>
    );
}
