import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { BaseWidget } from './BaseWidget';
import { Sparkles, TrendingUp, Clock, CheckCircle2 } from 'lucide-react';
import { useI18n } from '@/lib/i18n/I18nProvider';
import type { Widget, SharedData } from '@/types';

interface WelcomeStats {
    quick_actions: number;
    status: string;
    pending: number;
    completed: number;
}

/**
 * Widget de bienvenida del dashboard con diseño mejorado
 */
export function WelcomeWidget({ widget, onRefresh }: { widget: Widget; onRefresh?: () => void }) {
    const { auth } = usePage<SharedData>().props;
    const { t } = useI18n();
    const [stats, setStats] = useState<WelcomeStats | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        setIsLoading(true);
        try {
            const { widgetApi } = await import('@/services/widgetApi');
            const response = await widgetApi.getWelcome();
            
            if (response.success) {
                setStats(response.data);
            }
        } catch (error) {
            console.error('Error loading welcome stats:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const handleRefresh = () => {
        loadData();
        onRefresh?.();
    };

    const getGreeting = () => {
        const hour = new Date().getHours();
        if (hour < 12) return t('Good morning');
        if (hour < 18) return t('Good afternoon');
        return t('Good evening');
    };

    const getGreetingIcon = () => {
        const hour = new Date().getHours();
        if (hour < 12) return '🌅';
        if (hour < 18) return '☀️';
        return '🌙';
    };

    const currentDate = new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    return (
        <BaseWidget 
            title={widget.title} 
            description={widget.description}
            isLoading={isLoading}
            onRefresh={handleRefresh}
        >
            <div className="space-y-6">
                {/* Hero Section */}
                <div className="relative overflow-hidden rounded-xl bg-gradient-to-br from-primary/10 via-primary/5 to-transparent p-6 border border-primary/20">
                    <div className="relative z-10">
                        <div className="flex items-center gap-3 mb-2">
                            <span className="text-4xl">{getGreetingIcon()}</span>
                            <div>
                                <h3 className="text-3xl font-bold bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                                    {getGreeting()}, {auth.user.name}!
                                </h3>
                                <p className="text-sm text-muted-foreground mt-1">{currentDate}</p>
                            </div>
                        </div>
                        <p className="text-muted-foreground mt-3">
                            {t("Welcome back to your dashboard. Here's your overview for today.")}
                        </p>
                    </div>

                    {/* Decorative Elements */}
                    <div className="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl" />
                    <div className="absolute bottom-0 left-0 w-48 h-48 bg-primary/5 rounded-full blur-3xl" />
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard
                        icon={<Sparkles className="h-5 w-5" />}
                        label={t('Quick Actions')}
                        value={t('Ready')}
                        color="from-blue-500 to-cyan-500"
                    />
                    <StatCard
                        icon={<TrendingUp className="h-5 w-5" />}
                        label={t('Performance')}
                        value={stats?.status === 'active' ? t('Active') : t('Pending')}
                        color="from-green-500 to-emerald-500"
                    />
                    <StatCard
                        icon={<Clock className="h-5 w-5" />}
                        label={t('Pending')}
                        value={stats?.pending?.toString() || '0'}
                        color="from-orange-500 to-amber-500"
                    />
                    <StatCard
                        icon={<CheckCircle2 className="h-5 w-5" />}
                        label={t('Completed')}
                        value={stats?.completed?.toString() || '0'}
                        color="from-purple-500 to-pink-500"
                    />
                </div>
            </div>
        </BaseWidget>
    );
}

/**
 * Tarjeta de estadística individual
 */
function StatCard({
    icon,
    label,
    value,
    color,
}: {
    icon: React.ReactNode;
    label: string;
    value: string;
    color: string;
}) {
    return (
        <div className="group relative overflow-hidden rounded-lg border bg-card p-4 transition-all hover:shadow-md hover:scale-105">
            {/* Icon with gradient */}
            <div className={`inline-flex p-2 rounded-lg bg-gradient-to-br ${color} text-white mb-3`}>
                {icon}
            </div>

            {/* Content */}
            <div>
                <p className="text-xs text-muted-foreground">{label}</p>
                <p className="text-2xl font-bold mt-1">{value}</p>
            </div>

            {/* Hover gradient effect */}
            <div
                className={`absolute inset-0 bg-gradient-to-br ${color} opacity-0 group-hover:opacity-5 transition-opacity`}
            />
        </div>
    );
}
