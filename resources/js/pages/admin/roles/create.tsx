import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, useForm } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useMemo } from 'react'

export default function AdminRolesCreate({ permissions }: { permissions: string[] }) {
    const { t } = useI18n()
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        permissions: [] as string[],
    })

    const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
        { title: t('Administración'), href: '/admin/users' },
        { title: t('Roles'), href: '/admin/roles' },
        { title: t('Crear'), href: '/admin/roles/create' },
    ], [t])

    const submit = (e: React.FormEvent) => {
        e.preventDefault()
        post('/admin/roles')
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Crear rol')} />
            <form onSubmit={submit} className="flex flex-col gap-4 p-4">
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="name">{t('Nombre')}</Label>
                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                    </div>
                    <div className="col-span-full flex flex-col gap-2">
                        <Label>{t('Permisos')}</Label>
                        <div className="flex flex-wrap gap-3">
                            {permissions.map((p) => (
                                <label key={p} className="inline-flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={data.permissions.includes(p)}
                                        onChange={(e) => {
                                            if (e.target.checked) setData('permissions', [...data.permissions, p])
                                            else setData('permissions', data.permissions.filter((x) => x !== p))
                                        }}
                                    />
                                    <span>{p}</span>
                                </label>
                            ))}
                        </div>
                        {errors.permissions && <p className="text-sm text-red-500">{errors.permissions as any}</p>}
                    </div>
                </div>
                <div className="mt-2 flex items-center gap-2">
                    <Button type="submit" disabled={processing}>{t('Guardar')}</Button>
                    <Link href="/admin/roles" className="text-sm text-muted-foreground hover:underline">{t('Cancelar')}</Link>
                </div>
            </form>
        </AppLayout>
    )
}
