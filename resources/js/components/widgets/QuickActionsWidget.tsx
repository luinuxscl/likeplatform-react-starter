import { BaseWidget } from './BaseWidget';
import { Link } from '@inertiajs/react';
import {
    Plus,
    Users,
    FileText,
    Settings,
    BarChart3,
    Mail,
    Calendar,
    Download,
} from 'lucide-react';
import type { Widget } from '@/types';

interface QuickAction {
    icon: React.ReactNode;
    label: string;
    description: string;
    href: string;
    color: string;
}

/**
 * Widget de acciones rápidas con botones atractivos
 */
export function QuickActionsWidget({
    widget,
    onRefresh,
}: {
    widget: Widget;
    onRefresh?: () => void;
}) {
    const actions: QuickAction[] = [
        {
            icon: <Plus className="h-5 w-5" />,
            label: 'New User',
            description: 'Create a new user account',
            href: '/admin/users/create',
            color: 'from-blue-500 to-cyan-500',
        },
        {
            icon: <FileText className="h-5 w-5" />,
            label: 'New Report',
            description: 'Generate a new report',
            href: '/reports/create',
            color: 'from-purple-500 to-pink-500',
        },
        {
            icon: <Users className="h-5 w-5" />,
            label: 'Manage Users',
            description: 'View and edit users',
            href: '/admin/users',
            color: 'from-green-500 to-emerald-500',
        },
        {
            icon: <BarChart3 className="h-5 w-5" />,
            label: 'Analytics',
            description: 'View detailed analytics',
            href: '/analytics',
            color: 'from-orange-500 to-red-500',
        },
        {
            icon: <Mail className="h-5 w-5" />,
            label: 'Send Email',
            description: 'Send bulk emails',
            href: '/emails/compose',
            color: 'from-indigo-500 to-blue-500',
        },
        {
            icon: <Calendar className="h-5 w-5" />,
            label: 'Schedule',
            description: 'Manage your schedule',
            href: '/calendar',
            color: 'from-pink-500 to-rose-500',
        },
        {
            icon: <Download className="h-5 w-5" />,
            label: 'Export Data',
            description: 'Download reports',
            href: '/exports',
            color: 'from-teal-500 to-cyan-500',
        },
        {
            icon: <Settings className="h-5 w-5" />,
            label: 'Settings',
            description: 'Configure system',
            href: '/settings',
            color: 'from-gray-500 to-slate-500',
        },
    ];

    return (
        <BaseWidget
            title={widget.title}
            description={widget.description}
            onRefresh={onRefresh}
        >
            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                {actions.map((action, index) => (
                    <ActionCard key={index} action={action} />
                ))}
            </div>
        </BaseWidget>
    );
}

/**
 * Tarjeta de acción individual
 */
function ActionCard({ action }: { action: QuickAction }) {
    return (
        <Link
            href={action.href}
            className="group relative overflow-hidden rounded-lg border bg-card p-4 transition-all hover:shadow-lg hover:scale-105"
        >
            {/* Gradient Background */}
            <div
                className={`absolute inset-0 bg-gradient-to-br ${action.color} opacity-0 group-hover:opacity-10 transition-opacity`}
            />

            {/* Content */}
            <div className="relative space-y-2">
                {/* Icon */}
                <div
                    className={`inline-flex p-2 rounded-lg bg-gradient-to-br ${action.color} text-white`}
                >
                    {action.icon}
                </div>

                {/* Text */}
                <div>
                    <div className="font-semibold text-sm group-hover:text-primary transition-colors">
                        {action.label}
                    </div>
                    <div className="text-xs text-muted-foreground line-clamp-1">
                        {action.description}
                    </div>
                </div>
            </div>

            {/* Hover Effect Border */}
            <div
                className={`absolute inset-0 rounded-lg bg-gradient-to-br ${action.color} opacity-0 group-hover:opacity-20 transition-opacity pointer-events-none`}
            />
        </Link>
    );
}
