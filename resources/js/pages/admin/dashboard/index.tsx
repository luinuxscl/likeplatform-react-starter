import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

type RecentUser = {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  created_at: string
}

type DashboardProps = {
  kpis: {
    total_users: number
    new_users_7d: number
    verified_users: number
    roles_count: number
  }
  recent_users: RecentUser[]
}

export default function AdminDashboardIndex() {
  const { t } = useI18n()
  const page = usePage<{ props: DashboardProps }>()
  const { kpis, recent_users } = page.props as unknown as DashboardProps

  const breadcrumbs: BreadcrumbItem[] = [
    { title: t('Administración'), href: '/admin/dashboard' },
    { title: t('Dashboard'), href: '/admin/dashboard' },
  ]

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={t('Administración - Dashboard')} />

      <div className="flex flex-col gap-6 p-4">
        {/* KPIs */}
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="pb-2"><CardTitle className="text-sm">{t('Usuarios totales')}</CardTitle></CardHeader>
            <CardContent className="text-2xl font-semibold">{kpis.total_users}</CardContent>
          </Card>
          <Card>
            <CardHeader className="pb-2"><CardTitle className="text-sm">{t('Nuevos 7 días')}</CardTitle></CardHeader>
            <CardContent className="text-2xl font-semibold">{kpis.new_users_7d}</CardContent>
          </Card>
          <Card>
            <CardHeader className="pb-2"><CardTitle className="text-sm">{t('Verificados')}</CardTitle></CardHeader>
            <CardContent className="text-2xl font-semibold">{kpis.verified_users}</CardContent>
          </Card>
          <Card>
            <CardHeader className="pb-2"><CardTitle className="text-sm">{t('Roles')}</CardTitle></CardHeader>
            <CardContent className="text-2xl font-semibold">{kpis.roles_count}</CardContent>
          </Card>
        </div>

        {/* Accesos rápidos */}
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <Link href="/admin/users"><Button className="w-full" variant="secondary">{t('Gestionar usuarios')}</Button></Link>
          <Link href="/admin/roles"><Button className="w-full" variant="secondary">{t('Gestionar roles')}</Button></Link>
          <Link href="/admin/permissions"><Button className="w-full" variant="secondary">{t('Gestionar permisos')}</Button></Link>
          <Link href="/admin/options"><Button className="w-full" variant="secondary">{t('Opciones de la app')}</Button></Link>
        </div>

        {/* Usuarios recientes */}
        <Card>
          <CardHeader><CardTitle className="text-base">{t('Usuarios recientes')}</CardTitle></CardHeader>
          <CardContent>
            <div className="relative w-full overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="bg-neutral-100/50 text-xs uppercase dark:bg-neutral-800/40">
                  <tr>
                    <th className="px-4 py-3">{t('ID')}</th>
                    <th className="px-4 py-3">{t('Nombre')}</th>
                    <th className="px-4 py-3">{t('Email')}</th>
                    <th className="px-4 py-3">{t('Verificado')}</th>
                    <th className="px-4 py-3">{t('Creado')}</th>
                  </tr>
                </thead>
                <tbody>
                  {recent_users.length === 0 && (
                    <tr>
                      <td colSpan={5} className="px-4 py-6 text-center text-muted-foreground">{t('Sin registros')}</td>
                    </tr>
                  )}
                  {recent_users.map(u => (
                    <tr key={u.id} className="border-t border-sidebar-border/60">
                      <td className="px-4 py-3">{u.id}</td>
                      <td className="px-4 py-3">{u.name}</td>
                      <td className="px-4 py-3">{u.email}</td>
                      <td className="px-4 py-3">{u.email_verified_at ? t('Sí') : t('No')}</td>
                      <td className="px-4 py-3">{new Date(u.created_at).toLocaleString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  )
}
