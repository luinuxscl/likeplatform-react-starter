import AppLayout from '@/layouts/app-layout'
import admin from '@/routes/admin'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useEffect, useMemo, useState } from 'react'

type UserItem = {
    id: number
    name: string
    email: string
    email_verified_at: string | null
    roles: string[]
    created_at: string
}

type PaginationLink = {
    url: string | null
    label: string
    active: boolean
}

type UsersPageProps = {
    users: {
        data: UserItem[]
        links: PaginationLink[]
        current_page: number
        last_page: number
        per_page: number
        total: number
        from: number | null
        to: number | null
    }
    filters: {
        search: string
        perPage: number
    }
}

export default function AdminUsersIndex() {
    const { t } = useI18n()
    const page = usePage<{ props: UsersPageProps }>()
    const { users, filters } = page.props as unknown as UsersPageProps

    const [search, setSearch] = useState(filters.search ?? '')
    const [perPage, setPerPage] = useState<number>(filters.perPage ?? 10)

    // Debounce search
    useEffect(() => {
        const id = setTimeout(() => {
            router.get(
                admin.users.index().url,
                { search, perPage },
                { preserveState: true, preserveScroll: true, replace: true }
            )
        }, 350)
        return () => clearTimeout(id)
    }, [search, perPage])

    const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
        { title: t('Administración'), href: admin.users.index().url },
        { title: t('Usuarios'), href: admin.users.index().url },
    ], [t])

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Administración - Usuarios')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="text-lg font-medium">{t('Usuarios')}</div>
                    <div className="flex items-center gap-2">
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('Buscar por nombre o email...')}
                        />
                        <select
                            className="h-9 rounded-md border border-input bg-background px-2 text-sm"
                            value={perPage}
                            onChange={(e) => setPerPage(Number(e.target.value))}
                        >
                            {[10, 15, 25, 50].map((n) => (
                                <option key={n} value={n}>{n} / {t('página')}</option>
                            ))}
                        </select>
                        <Link href="/admin/users/create">
                            <Button variant="default">{t('Crear usuario')}</Button>
                        </Link>
                    </div>
                </div>

                <div className="relative overflow-hidden rounded-xl border border-border">
                    <div className="relative w-full overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-muted text-xs uppercase">
                                <tr>
                                    <th className="px-4 py-3">{t('ID')}</th>
                                    <th className="px-4 py-3">{t('Nombre')}</th>
                                    <th className="px-4 py-3">{t('Email')}</th>
                                    <th className="px-4 py-3">{t('Roles')}</th>
                                    <th className="px-4 py-3">{t('Verificado')}</th>
                                    <th className="px-4 py-3">{t('Creado')}</th>
                                    <th className="px-4 py-3 text-right">{t('Acciones')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-6 text-center text-muted-foreground">
                                            {t('No hay usuarios que coincidan con la búsqueda.')}
                                        </td>
                                    </tr>
                                )}
                                {users.data.map((u) => (
                                    <tr key={u.id} className="border-t border-border">
                                        <td className="px-4 py-3">{u.id}</td>
                                        <td className="px-4 py-3">{u.name}</td>
                                        <td className="px-4 py-3"><a className="text-primary hover:underline" href={`mailto:${u.email}`}>{u.email}</a></td>
                                        <td className="px-4 py-3">{u.roles.join(', ')}</td>
                                        <td className="px-4 py-3">{u.email_verified_at ? t('Sí') : t('No')}</td>
                                        <td className="px-4 py-3">{new Date(u.created_at).toLocaleString()}</td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link href={`/admin/users/${u.id}/edit`}>
                                                    <Button variant="default" size="sm">{t('Editar')}</Button>
                                                </Link>
                                                <Link
                                                    href={`/admin/users/${u.id}`}
                                                    method="delete"
                                                    as="button"
                                                    onBefore={() => confirm(t('¿Confirmas eliminar este usuario?'))}
                                                >
                                                    <Button variant="destructive" size="sm">{t('Eliminar')}</Button>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="flex items-center justify-between gap-2 border-t border-border p-3 text-sm">
                        <div className="text-muted-foreground">
                            {users.from ?? 0}-{users.to ?? 0} / {users.total}
                        </div>
                        <div className="flex flex-wrap items-center gap-1">
                            {users.links.map((link, idx) => (
                                <Link
                                    key={`${link.label}-${idx}`}
                                    href={link.url ?? '#'}
                                    preserveState
                                    preserveScroll
                                    className={[
                                        'rounded-md px-2 py-1',
                                        link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted',
                                        !link.url ? 'pointer-events-none opacity-50' : '',
                                    ].join(' ')}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    )
}
