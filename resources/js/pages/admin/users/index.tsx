import AppLayout from '@/layouts/app-layout'
import admin from '@/routes/admin'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'

export default function AdminUsersIndex() {
    const { t } = useI18n()

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Administración'), href: admin.users.index().url },
        { title: t('Usuarios') },
    ]

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Administración - Usuarios')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="text-sm text-muted-foreground">
                    {t('Pantalla base de administración de usuarios. Aquí listaremos, crearemos y editaremos usuarios.')}
                </div>
                <div className="relative min-h-[40vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border" />
            </div>
        </AppLayout>
    )
}
