import { useState, useEffect } from 'react';
import { TrendingUp, TrendingDown, DollarSign, Users, Activity } from 'lucide-react';
import { BaseWidget } from './BaseWidget';
import { LineChart, Line, ResponsiveContainer } from 'recharts';
import { useI18n } from '@/lib/i18n/I18nProvider';
import type { Widget } from '@/types';

interface StatsData {
    revenue: {
        current: number;
        previous: number;
        change: number;
        trend: number[];
    };
    users: {
        current: number;
        previous: number;
        change: number;
        trend: number[];
    };
    activity: {
        current: number;
        previous: number;
        change: number;
        trend: number[];
    };
}

/**
 * Widget de estadísticas con métricas y mini gráficos
 */
export function StatsWidget({
    widget,
    onRefresh,
}: {
    widget: Widget;
    onRefresh?: () => void;
}) {
    const { t } = useI18n();
    const [data, setData] = useState<StatsData | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        // Simular carga de datos (reemplazar con API real)
        setTimeout(() => {
            setData({
                revenue: {
                    current: 15231.89,
                    previous: 10156.23,
                    change: 50.1,
                    trend: [45, 52, 48, 65, 58, 72, 68, 85, 78, 92, 88, 95],
                },
                users: {
                    current: 2350,
                    previous: 1987,
                    change: 18.3,
                    trend: [20, 25, 22, 30, 28, 35, 32, 40, 38, 45, 42, 48],
                },
                activity: {
                    current: 8542,
                    previous: 7234,
                    change: 18.1,
                    trend: [60, 65, 62, 70, 68, 75, 72, 80, 78, 85, 82, 88],
                },
            });
            setIsLoading(false);
        }, 500);
    }, []);

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2,
        }).format(value);
    };

    const formatNumber = (value: number) => {
        return new Intl.NumberFormat('en-US').format(value);
    };

    return (
        <BaseWidget
            title={widget.title}
            description={widget.description}
            isLoading={isLoading}
            isEmpty={!data}
            onRefresh={onRefresh}
        >
            {data && (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {/* Revenue Card */}
                    <StatCard
                        icon={<DollarSign className="h-5 w-5" />}
                        label={t('Revenue')}
                        value={formatCurrency(data.revenue.current)}
                        change={data.revenue.change}
                        trend={data.revenue.trend}
                        subtitle={`${formatCurrency(data.revenue.previous)} ${t('vs last period')}`}
                    />

                    {/* Users Card */}
                    <StatCard
                        icon={<Users className="h-5 w-5" />}
                        label={t('Total Users')}
                        value={formatNumber(data.users.current)}
                        change={data.users.change}
                        trend={data.users.trend}
                        subtitle={`${formatNumber(data.users.previous)} ${t('vs last period')}`}
                    />

                    {/* Activity Card */}
                    <StatCard
                        icon={<Activity className="h-5 w-5" />}
                        label={t('Activity')}
                        value={formatNumber(data.activity.current)}
                        change={data.activity.change}
                        trend={data.activity.trend}
                        subtitle={`${formatNumber(data.activity.previous)} ${t('vs last period')}`}
                    />
                </div>
            )}
        </BaseWidget>
    );
}

/**
 * Tarjeta individual de estadística
 */
function StatCard({
    icon,
    label,
    value,
    change,
    trend,
    subtitle,
}: {
    icon: React.ReactNode;
    label: string;
    value: string;
    change: number;
    trend: number[];
    subtitle: string;
}) {
    const isPositive = change > 0;
    const chartData = trend.map((value, index) => ({ value, index }));

    return (
        <div className="relative overflow-hidden rounded-lg border bg-card p-6 transition-all hover:shadow-lg">
            {/* Header */}
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2 text-muted-foreground">
                    {icon}
                    <span className="text-sm font-medium">{label}</span>
                </div>
                <div
                    className={`flex items-center gap-1 text-sm font-semibold ${
                        isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                    }`}
                >
                    {isPositive ? (
                        <TrendingUp className="h-4 w-4" />
                    ) : (
                        <TrendingDown className="h-4 w-4" />
                    )}
                    {isPositive ? '+' : ''}
                    {change.toFixed(1)}%
                </div>
            </div>

            {/* Value */}
            <div className="mb-2">
                <div className="text-3xl font-bold">{value}</div>
                <div className="text-xs text-muted-foreground mt-1">{subtitle}</div>
            </div>

            {/* Mini Chart */}
            <div className="h-16 -mx-2 mt-4">
                <ResponsiveContainer width="100%" height="100%">
                    <LineChart data={chartData}>
                        <Line
                            type="monotone"
                            dataKey="value"
                            stroke="hsl(var(--primary))"
                            strokeWidth={2}
                            dot={false}
                        />
                    </LineChart>
                </ResponsiveContainer>
            </div>

            {/* Gradient Background Effect */}
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent pointer-events-none" />
        </div>
    );
}
