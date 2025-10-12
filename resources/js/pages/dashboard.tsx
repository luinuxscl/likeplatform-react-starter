import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useI18n } from '@/lib/i18n/I18nProvider';
import { WidgetDashboard } from '@/components/widgets';

// breadcrumbs se construyen en runtime para usar traducciones

export default function Dashboard() {
    const { t } = useI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('Dashboard'),
            href: dashboard().url,
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Dashboard')} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 lg:p-8">
                <WidgetDashboard
                    layout="grid"
                    columns={12}
                    allowCustomization={true}
                />
            </div>
        </AppLayout>
    );
}
