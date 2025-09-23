import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import ThemeSwitcherMini from '@/components/theme/theme-switcher-mini'
import { StatCard } from '@/components/ui/stat-card'

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
        <div className="flex items-center justify-between">
          <div />
          <ThemeSwitcherMini />
        </div>
        {/* KPIs (shadcn-inspired) */}
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard
            title={t('Usuarios totales')}
            value={kpis.total_users}
            trend={{ direction: 'up', value: '+12.5%' }}
            helper={t('Tendencia mensual positiva')}
          />
          <StatCard
            title={t('Nuevos 7 días')}
            value={kpis.new_users_7d}
            trend={{ direction: 'down', value: '-2.0%' }}
            helper={t('Variación semanal')}
          />
          <StatCard
            title={t('Verificados')}
            value={kpis.verified_users}
            trend={{ direction: 'up', value: '+8.1%' }}
            helper={t('Retención de usuarios')}
          />
          <StatCard
            title={t('Roles')}
            value={kpis.roles_count}
            trend={{ direction: 'flat', value: '0%' }}
            helper={t('Sin cambios recientes')}
          />
        </div>

        {/* Accesos rápidos */}
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <Link href="/admin/users"><Button className="w-full" variant="default">{t('Gestionar usuarios')}</Button></Link>
          <Link href="/admin/roles"><Button className="w-full" variant="default">{t('Gestionar roles')}</Button></Link>
          <Link href="/admin/permissions"><Button className="w-full" variant="default">{t('Gestionar permisos')}</Button></Link>
          <Link href="/admin/options"><Button className="w-full" variant="default">{t('Opciones de la app')}</Button></Link>
        </div>

        {/* Usuarios recientes */}
        <Card>
          <CardHeader><CardTitle className="text-base">{t('Usuarios recientes')}</CardTitle></CardHeader>
          <CardContent>
            <div className="relative w-full overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="bg-muted text-xs uppercase">
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
                    <tr key={u.id} className="border-t border-border hover:bg-accent/50">
                      <td className="px-4 py-3">{u.id}</td>
                      <td className="px-4 py-3">{u.name}</td>
                      <td className="px-4 py-3"><a className="text-primary hover:underline" href={`mailto:${u.email}`}>{u.email}</a></td>
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
