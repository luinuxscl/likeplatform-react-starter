import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, useForm } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useMemo } from 'react'

export default function AdminPermissionsCreate() {
    const { t } = useI18n()
    const { data, setData, post, processing, errors } = useForm({
        name: '',
    })

    const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
        { title: t('Administración'), href: '/admin/users' },
        { title: t('Permisos'), href: '/admin/permissions' },
        { title: t('Crear'), href: '/admin/permissions/create' },
    ], [t])

    const submit = (e: React.FormEvent) => {
        e.preventDefault()
        post('/admin/permissions')
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Crear permiso')} />
            <form onSubmit={submit} className="flex flex-col gap-4 p-4">
                <div className="grid gap-4 sm:grid-cols-1">
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="name">{t('Nombre')}</Label>
                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                    </div>
                </div>
                <div className="mt-2 flex items-center gap-2">
                    <Button type="submit" disabled={processing}>{t('Guardar')}</Button>
                    <Link href="/admin/permissions" className="text-sm text-muted-foreground hover:underline">{t('Cancelar')}</Link>
                </div>
            </form>
        </AppLayout>
    )
}
