import { useState, useEffect } from 'react';
import { BaseWidget } from './BaseWidget';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, ResponsiveContainer, Cell, Tooltip } from 'recharts';
import { Button } from '@/components/ui/button';
import { Plus, Minus } from 'lucide-react';
import { useI18n } from '@/lib/i18n/I18nProvider';
import type { Widget } from '@/types';

interface GoalData {
    day: string;
    value: number;
    target: number;
}

/**
 * Widget de objetivos con gráfico de barras interactivo
 */
export function GoalsWidget({
    widget,
    onRefresh,
}: {
    widget: Widget;
    onRefresh?: () => void;
}) {
    const { t } = useI18n();
    const [data, setData] = useState<GoalData[] | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [dailyGoal, setDailyGoal] = useState(350);

    useEffect(() => {
        loadData();
    }, [dailyGoal]);

    const weekDays = [t('Mon'), t('Tue'), t('Wed'), t('Thu'), t('Fri'), t('Sat'), t('Sun')];

    const loadData = () => {
        setIsLoading(true);
        // Simular carga de datos (reemplazar con API real)
        setTimeout(() => {
            setData([
                { day: 'Mon', value: 280, target: dailyGoal },
                { day: 'Tue', value: 320, target: dailyGoal },
                { day: 'Wed', value: 380, target: dailyGoal },
                { day: 'Thu', value: 290, target: dailyGoal },
                { day: 'Fri', value: 410, target: dailyGoal },
                { day: 'Sat', value: 360, target: dailyGoal },
                { day: 'Sun', value: 390, target: dailyGoal },
            ]);
            setIsLoading(false);
        }, 300);
    };

    const handleRefresh = () => {
        loadData();
        onRefresh?.();
    };

    const adjustGoal = (amount: number) => {
        setDailyGoal((prev) => Math.max(50, prev + amount));
    };

    // Calcular estadísticas
    const totalValue = data?.reduce((sum, item) => sum + item.value, 0) || 0;
    const totalTarget = data?.reduce((sum, item) => sum + item.target, 0) || 0;
    const achievement = totalTarget > 0 ? (totalValue / totalTarget) * 100 : 0;
    const daysAchieved = data?.filter((item) => item.value >= item.target).length || 0;

    return (
        <BaseWidget
            title={widget.title}
            description={widget.description}
            isLoading={isLoading}
            isEmpty={!data}
            onRefresh={handleRefresh}
        >
            {data && (
                <div className="space-y-6">
                    {/* Goal Control */}
                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <div className="text-sm text-muted-foreground">{t('Daily Goal')}</div>
                            <div className="text-2xl font-bold">{dailyGoal}</div>
                        </div>
                        <div className="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="icon"
                                onClick={() => adjustGoal(-50)}
                                className="h-8 w-8"
                            >
                                <Minus className="h-4 w-4" />
                            </Button>
                            <Button
                                variant="outline"
                                size="icon"
                                onClick={() => adjustGoal(50)}
                                className="h-8 w-8"
                            >
                                <Plus className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    {/* Stats */}
                    <div className="grid grid-cols-3 gap-4">
                        <div className="text-center">
                            <div className="text-2xl font-bold text-primary">
                                {achievement.toFixed(0)}%
                            </div>
                            <div className="text-xs text-muted-foreground mt-1">
                                {t('Achievement')}
                            </div>
                        </div>
                        <div className="text-center">
                            <div className="text-2xl font-bold">
                                {daysAchieved}/{data.length}
                            </div>
                            <div className="text-xs text-muted-foreground mt-1">
                                {t('Days Achieved')}
                            </div>
                        </div>
                        <div className="text-center">
                            <div className="text-2xl font-bold">
                                {totalValue.toLocaleString()}
                            </div>
                            <div className="text-xs text-muted-foreground mt-1">
                                {t('Total This Week')}
                            </div>
                        </div>
                    </div>

                    {/* Bar Chart */}
                    <div className="h-48 w-full">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart
                                data={data}
                                margin={{ top: 10, right: 10, left: -20, bottom: 0 }}
                            >
                                <CartesianGrid
                                    strokeDasharray="3 3"
                                    stroke="hsl(var(--border))"
                                    opacity={0.3}
                                    vertical={false}
                                />
                                <XAxis
                                    dataKey="day"
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
                                />
                                <Bar
                                    dataKey="value"
                                    radius={[8, 8, 0, 0]}
                                    maxBarSize={50}
                                >
                                    {data.map((entry, index) => (
                                        <Cell
                                            key={`cell-${index}`}
                                            fill={
                                                entry.value >= entry.target
                                                    ? 'hsl(var(--primary))'
                                                    : 'hsl(var(--muted-foreground))'
                                            }
                                            opacity={entry.value >= entry.target ? 1 : 0.3}
                                        />
                                    ))}
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                    </div>

                    {/* Goal Line Indicator */}
                    <div className="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                        <div className="flex items-center gap-2">
                            <div className="w-3 h-3 rounded bg-primary" />
                            <span>Goal Achieved</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="w-3 h-3 rounded bg-muted-foreground/30" />
                            <span>Below Goal</span>
                        </div>
                    </div>
                </div>
            )}
        </BaseWidget>
    );
}
