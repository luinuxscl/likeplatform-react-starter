import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useEffect, useMemo, useState } from 'react'

type RoleItem = {
    id: number
    name: string
    permissions: string[]
}

type PaginationLink = {
    url: string | null
    label: string
    active: boolean
}

type RolesPageProps = {
    roles: {
        data: RoleItem[]
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

export default function AdminRolesIndex() {
    const { t } = useI18n()
    const page = usePage<{ props: RolesPageProps }>()
    const { roles, filters } = page.props as unknown as RolesPageProps

    const [search, setSearch] = useState(filters.search ?? '')
    const [perPage, setPerPage] = useState<number>(filters.perPage ?? 10)

    useEffect(() => {
        const id = setTimeout(() => {
            router.get(
                '/admin/roles',
                { search, perPage },
                { preserveState: true, preserveScroll: true, replace: true }
            )
        }, 350)
        return () => clearTimeout(id)
    }, [search, perPage])

    const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
        { title: t('Administración'), href: '/admin/users' },
        { title: t('Roles'), href: '/admin/roles' },
    ], [t])

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Administración - Roles')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="text-lg font-medium">{t('Roles')}</div>
                    <div className="flex items-center gap-2">
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('Buscar por nombre...')}
                        />
                        <select
                            className="h-9 rounded-md border bg-transparent px-2 text-sm"
                            value={perPage}
                            onChange={(e) => setPerPage(Number(e.target.value))}
                        >
                            {[10, 15, 25, 50].map((n) => (
                                <option key={n} value={n}>{n} / {t('página')}</option>
                            ))}
                        </select>
                        <Link href="/admin/roles/create">
                            <Button>{t('Crear rol')}</Button>
                        </Link>
                    </div>
                </div>

                <div className="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div className="relative w-full overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-neutral-100/50 text-xs uppercase dark:bg-neutral-800/40">
                                <tr>
                                    <th className="px-4 py-3">{t('ID')}</th>
                                    <th className="px-4 py-3">{t('Nombre')}</th>
                                    <th className="px-4 py-3">{t('Permisos')}</th>
                                    <th className="px-4 py-3 text-right">{t('Acciones')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {roles.data.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-4 py-6 text-center text-muted-foreground">
                                            {t('No hay roles que coincidan con la búsqueda.')}
                                        </td>
                                    </tr>
                                )}
                                {roles.data.map((r) => (
                                    <tr key={r.id} className="border-t border-sidebar-border/60">
                                        <td className="px-4 py-3">{r.id}</td>
                                        <td className="px-4 py-3">{r.name}</td>
                                        <td className="px-4 py-3">{r.permissions.join(', ')}</td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link href={`/admin/roles/${r.id}/edit`}>
                                                    <Button variant="secondary" size="sm">{t('Editar')}</Button>
                                                </Link>
                                                <Link
                                                    href={`/admin/roles/${r.id}`}
                                                    method="delete"
                                                    as="button"
                                                    onBefore={() => confirm(t('¿Confirmas eliminar este rol?'))}
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
                    <div className="flex items-center justify-between gap-2 border-t border-sidebar-border/60 p-3 text-sm">
                        <div className="text-muted-foreground">
                            {roles.from ?? 0}-{roles.to ?? 0} / {roles.total}
                        </div>
                        <div className="flex flex-wrap items-center gap-1">
                            {roles.links.map((link, idx) => (
                                <Link
                                    key={`${link.label}-${idx}`}
                                    href={link.url ?? '#'}
                                    preserveState
                                    preserveScroll
                                    className={[
                                        'rounded-md px-2 py-1',
                                        link.active ? 'bg-neutral-200 dark:bg-neutral-800' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800/60',
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
