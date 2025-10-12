import { usePage } from '@inertiajs/react';
import { BaseWidget } from './BaseWidget';
import type { Widget, SharedData } from '@/types';

/**
 * Widget de bienvenida del dashboard
 */
export function WelcomeWidget({ widget, onRefresh }: { widget: Widget; onRefresh?: () => void }) {
    const { auth } = usePage<SharedData>().props;

    const getGreeting = () => {
        const hour = new Date().getHours();
        if (hour < 12) return 'Good morning';
        if (hour < 18) return 'Good afternoon';
        return 'Good evening';
    };

    return (
        <BaseWidget title={widget.title} description={widget.description}>
            <div className="space-y-4">
                <div>
                    <h3 className="text-2xl font-semibold">
                        {getGreeting()}, {auth.user.name}!
                    </h3>
                    <p className="text-muted-foreground mt-2">
                        Welcome to your dashboard. Here's what's happening today.
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div className="p-4 rounded-lg bg-primary/10">
                        <p className="text-sm text-muted-foreground">Quick Actions</p>
                        <p className="text-2xl font-bold mt-1">Ready</p>
                    </div>
                    <div className="p-4 rounded-lg bg-primary/10">
                        <p className="text-sm text-muted-foreground">Status</p>
                        <p className="text-2xl font-bold mt-1">Active</p>
                    </div>
                    <div className="p-4 rounded-lg bg-primary/10">
                        <p className="text-sm text-muted-foreground">Updates</p>
                        <p className="text-2xl font-bold mt-1">0</p>
                    </div>
                </div>
            </div>
        </BaseWidget>
    );
}
