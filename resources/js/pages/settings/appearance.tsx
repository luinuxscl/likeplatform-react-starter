import { Head } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance-tabs';
import HeadingSmall from '@/components/heading-small';
import ThemeSelector from '@/components/expansion/themes/ThemeSelector';
import ThemeLivePreview from '@/components/expansion/themes/ThemeLivePreview';
import LanguageSelector from '@/components/expansion/i18n/LanguageSelector';
import { type BreadcrumbItem } from '@/types';
import { useI18n } from '@/lib/i18n/I18nProvider';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit as editAppearance } from '@/routes/appearance';

// Breadcrumbs dinámicos para i18n

export default function Appearance() {
    const { t } = useI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('Appearance settings'),
            href: editAppearance().url,
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Appearance settings')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title={t('Appearance settings')} description={t("Update your account's appearance settings")} />
                    <ThemeSelector />
                    <ThemeLivePreview />
                    <LanguageSelector />
                    <AppearanceTabs />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
