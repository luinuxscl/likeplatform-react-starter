import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, useForm } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useMemo } from 'react'

export default function AdminUsersCreate({ roles }: { roles: string[] }) {
    const { t } = useI18n()
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        verified: false as boolean | undefined,
        roles: [] as string[],
    })

    const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
        { title: t('Administración'), href: '/admin/users' },
        { title: t('Usuarios'), href: '/admin/users' },
        { title: t('Crear'), href: '/admin/users/create' },
    ], [t])

    const submit = (e: React.FormEvent) => {
        e.preventDefault()
        post('/admin/users')
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Crear usuario')} />
            <form onSubmit={submit} className="flex flex-col gap-4 p-4">
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="name">{t('Nombre')}</Label>
                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="email">{t('Email')}</Label>
                        <Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                        {errors.email && <p className="text-sm text-red-500">{errors.email}</p>}
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="password">{t('Password')}</Label>
                        <Input id="password" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} />
                        {errors.password && <p className="text-sm text-red-500">{errors.password}</p>}
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="verified">{t('Verificar email')}</Label>
                        <input id="verified" type="checkbox" checked={!!data.verified} onChange={(e) => setData('verified', e.target.checked)} />
                    </div>
                    <div className="col-span-full flex flex-col gap-2">
                        <Label>{t('Roles')}</Label>
                        <div className="flex flex-wrap gap-3">
                            {roles.map((r) => (
                                <label key={r} className="inline-flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={data.roles.includes(r)}
                                        onChange={(e) => {
                                            if (e.target.checked) setData('roles', [...data.roles, r])
                                            else setData('roles', data.roles.filter((x) => x !== r))
                                        }}
                                    />
                                    <span>{r}</span>
                                </label>
                            ))}
                        </div>
                        {errors.roles && <p className="text-sm text-red-500">{errors.roles as any}</p>}
                    </div>
                </div>
                <div className="mt-2 flex items-center gap-2">
                    <Button type="submit" disabled={processing}>{t('Guardar')}</Button>
                    <Link href="/admin/users" className="text-sm text-muted-foreground hover:underline">{t('Cancelar')}</Link>
                </div>
            </form>
        </AppLayout>
    )
}
