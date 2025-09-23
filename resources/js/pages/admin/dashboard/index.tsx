import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Card, CardAction, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import ThemeSwitcherMini from '@/components/theme/theme-switcher-mini'
import { Badge } from '@/components/ui/badge'
import { ArrowUpRight, ArrowDownRight } from 'lucide-react'

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

  const formatNumber = (n: number) => new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(n)
  const trends = (page.props as any)?.trends as
    | { total_users?: string; new_users_7d?: string; verified_users?: string; roles_count?: string }
    | undefined

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
        {/* KPIs (shadcn example pattern) */}
        <div className="*:data-[slot=card]:from-primary/3 *:data-[slot=card]:to-card dark:*:data-[slot=card]:bg-card grid grid-cols-1 gap-4 *:data-[slot=card]:bg-gradient-to-t *:data-[slot=card]:shadow-xs sm:grid-cols-2 lg:grid-cols-4">
          <Card className="@container/card rounded-2xl">
            <CardHeader>
              <div className="flex items-baseline gap-2">
                <div className="flex min-w-0 flex-col gap-1.5">
                  <CardDescription>{t('Usuarios totales')}</CardDescription>
                  <CardTitle className="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">{formatNumber(kpis.total_users)}</CardTitle>
                </div>
                <CardAction className="self-start">
                  <Badge variant="outline" aria-label={t('Tendencia: +12.5%')}>
                    <ArrowUpRight className="h-3.5 w-3.5" aria-hidden="true" />
                    {trends?.total_users ?? '+12.5%'}
                  </Badge>
                </CardAction>
              </div>
            </CardHeader>
            <CardFooter className="flex-col items-start gap-1.5 text-sm">
              <div className="line-clamp-1 flex gap-2 font-medium">
                {t('Tendencia mensual positiva')} <ArrowUpRight className="size-4" />
              </div>
              <div className="text-muted-foreground">{t('Últimos 6 meses')}</div>
            </CardFooter>
          </Card>

          <Card className="@container/card rounded-2xl">
            <CardHeader>
              <div className="flex items-baseline gap-2">
                <div className="flex min-w-0 flex-col gap-1.5">
                  <CardDescription>{t('Nuevos 7 días')}</CardDescription>
                  <CardTitle className="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">{formatNumber(kpis.new_users_7d)}</CardTitle>
                </div>
                <CardAction className="self-start">
                  <Badge variant="outline" aria-label={t('Tendencia: -2.0%')}>
                    <ArrowDownRight className="h-3.5 w-3.5" aria-hidden="true" />
                    {trends?.new_users_7d ?? '-2.0%'}
                  </Badge>
                </CardAction>
              </div>
            </CardHeader>
            <CardFooter className="flex-col items-start gap-1.5 text-sm">
              <div className="line-clamp-1 flex gap-2 font-medium">
                {t('Variación semanal')} <ArrowDownRight className="size-4" />
              </div>
              <div className="text-muted-foreground">{t('Requiere atención')}</div>
            </CardFooter>
          </Card>

          <Card className="@container/card rounded-2xl">
            <CardHeader>
              <div className="flex items-baseline gap-2">
                <div className="flex min-w-0 flex-col gap-1.5">
                  <CardDescription>{t('Verificados')}</CardDescription>
                  <CardTitle className="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">{formatNumber(kpis.verified_users)}</CardTitle>
                </div>
                <CardAction className="self-start">
                  <Badge variant="outline" aria-label={t('Tendencia: +8.1%')}>
                    <ArrowUpRight className="h-3.5 w-3.5" aria-hidden="true" />
                    {trends?.verified_users ?? '+8.1%'}
                  </Badge>
                </CardAction>
              </div>
            </CardHeader>
            <CardFooter className="flex-col items-start gap-1.5 text-sm">
              <div className="line-clamp-1 flex gap-2 font-medium">
                {t('Retención de usuarios')} <ArrowUpRight className="size-4" />
              </div>
              <div className="text-muted-foreground">{t('Supera objetivo')}</div>
            </CardFooter>
          </Card>

          <Card className="@container/card rounded-2xl">
            <CardHeader>
              <div className="flex items-baseline gap-2">
                <div className="flex min-w-0 flex-col gap-1.5">
                  <CardDescription>{t('Roles')}</CardDescription>
                  <CardTitle className="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">{formatNumber(kpis.roles_count)}</CardTitle>
                </div>
                <CardAction className="self-start">
                  <Badge variant="outline" aria-label={t('Tendencia: 0%')}>{trends?.roles_count ?? '0%'}</Badge>
                </CardAction>
              </div>
            </CardHeader>
            <CardFooter className="flex-col items-start gap-1.5 text-sm">
              <div className="line-clamp-1 flex gap-2 font-medium">
                {t('Sin cambios recientes')}
              </div>
              <div className="text-muted-foreground">{t('Estable')}</div>
            </CardFooter>
          </Card>
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
