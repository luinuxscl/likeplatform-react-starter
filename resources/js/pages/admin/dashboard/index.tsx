import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Card, CardAction, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import ThemeSwitcherMini from '@/components/theme/theme-switcher-mini'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { ArrowUpRight, ArrowDownRight, Users as UsersIcon, UserCheck, Shield, Settings, Package, KeySquare } from 'lucide-react'

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
  const user = (page.props as any)?.auth?.user

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
        {/* Hero Section */}
        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary/10 via-primary/5 to-background p-6 shadow-sm border border-primary/10">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Avatar className="h-16 w-16 border-2 border-primary/20">
                <AvatarImage src={user?.avatar} alt={user?.name} />
                <AvatarFallback className="bg-primary/10 text-primary font-semibold text-lg">
                  {user?.name?.charAt(0)?.toUpperCase() || 'A'}
                </AvatarFallback>
              </Avatar>
              <div>
                <h1 className="text-2xl font-bold">{t('Hola')}, {user?.name || 'Admin'}</h1>
                <div className="flex items-center gap-2 mt-1">
                  <Badge variant="secondary" className="bg-primary/10 text-primary border-primary/20">
                    <Shield className="h-3 w-3 mr-1" />
                    {t('Administrador')}
                  </Badge>
                </div>
              </div>
            </div>
            <ThemeSwitcherMini />
          </div>
        </div>
        {/* KPIs (shadcn example pattern) */}
        <div className="*:data-[slot=card]:from-primary/3 *:data-[slot=card]:to-card dark:*:data-[slot=card]:bg-card grid grid-cols-1 gap-4 *:data-[slot=card]:bg-gradient-to-t *:data-[slot=card]:shadow-xs sm:grid-cols-2 lg:grid-cols-4">
          <Card className="@container/card rounded-2xl border-l-4 border-l-primary hover:shadow-md transition-shadow">
            <CardHeader>
              <div className="flex items-baseline gap-2">
                <div className="flex min-w-0 flex-col gap-1.5">
                  <div className="flex items-center gap-2">
                    <UsersIcon className="h-4 w-4 text-primary" />
                    <CardDescription>{t('Usuarios totales')}</CardDescription>
                  </div>
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

          <Card className="@container/card rounded-2xl border-l-4 border-l-blue-500 hover:shadow-md transition-shadow">
            <CardHeader>
              <div className="flex items-baseline gap-2">
                <div className="flex min-w-0 flex-col gap-1.5">
                  <div className="flex items-center gap-2">
                    <UserCheck className="h-4 w-4 text-blue-500" />
                    <CardDescription>{t('Nuevos 7 días')}</CardDescription>
                  </div>
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

          <Card className="@container/card rounded-2xl border-l-4 border-l-green-500 hover:shadow-md transition-shadow">
            <CardHeader>
              <div className="flex items-baseline gap-2">
                <div className="flex min-w-0 flex-col gap-1.5">
                  <div className="flex items-center gap-2">
                    <UserCheck className="h-4 w-4 text-green-500" />
                    <CardDescription>{t('Verificados')}</CardDescription>
                  </div>
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

          <Card className="@container/card rounded-2xl border-l-4 border-l-purple-500 hover:shadow-md transition-shadow">
            <CardHeader>
              <div className="flex items-baseline gap-2">
                <div className="flex min-w-0 flex-col gap-1.5">
                  <div className="flex items-center gap-2">
                    <Shield className="h-4 w-4 text-purple-500" />
                    <CardDescription>{t('Roles')}</CardDescription>
                  </div>
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

        {/* Quick Actions */}
        <div>
          <h2 className="text-lg font-semibold mb-3">{t('Acciones Rápidas')}</h2>
          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <Link href="/admin/users">
              <Card className="group hover:border-primary/50 hover:shadow-md transition-all cursor-pointer h-full">
                <CardHeader className="pb-3">
                  <UsersIcon className="h-8 w-8 text-primary mb-2 group-hover:scale-110 transition-transform" />
                  <CardTitle className="text-sm">{t('Gestionar usuarios')}</CardTitle>
                  <CardDescription className="text-xs">{t('Ver y editar usuarios')}</CardDescription>
                </CardHeader>
                <CardContent>
                  <Button size="sm" variant="ghost" className="w-full group-hover:bg-primary/10">
                    {t('Acceder')} →
                  </Button>
                </CardContent>
              </Card>
            </Link>
            <Link href="/admin/roles">
              <Card className="group hover:border-primary/50 hover:shadow-md transition-all cursor-pointer h-full">
                <CardHeader className="pb-3">
                  <Shield className="h-8 w-8 text-primary mb-2 group-hover:scale-110 transition-transform" />
                  <CardTitle className="text-sm">{t('Gestionar roles')}</CardTitle>
                  <CardDescription className="text-xs">{t('Configurar roles y permisos')}</CardDescription>
                </CardHeader>
                <CardContent>
                  <Button size="sm" variant="ghost" className="w-full group-hover:bg-primary/10">
                    {t('Acceder')} →
                  </Button>
                </CardContent>
              </Card>
            </Link>
            <Link href="/admin/package-settings">
              <Card className="group hover:border-primary/50 hover:shadow-md transition-all cursor-pointer h-full">
                <CardHeader className="pb-3">
                  <Package className="h-8 w-8 text-primary mb-2 group-hover:scale-110 transition-transform" />
                  <CardTitle className="text-sm">{t('Configurar paquetes')}</CardTitle>
                  <CardDescription className="text-xs">{t('Gestionar extensiones')}</CardDescription>
                </CardHeader>
                <CardContent>
                  <Button size="sm" variant="ghost" className="w-full group-hover:bg-primary/10">
                    {t('Acceder')} →
                  </Button>
                </CardContent>
              </Card>
            </Link>
            <Link href="/admin/api-keys">
              <Card className="group hover:border-primary/50 hover:shadow-md transition-all cursor-pointer h-full">
                <CardHeader className="pb-3">
                  <KeySquare className="h-8 w-8 text-primary mb-2 group-hover:scale-110 transition-transform" />
                  <CardTitle className="text-sm">{t('API Keys')}</CardTitle>
                  <CardDescription className="text-xs">{t('Gestionar claves de API')}</CardDescription>
                </CardHeader>
                <CardContent>
                  <Button size="sm" variant="ghost" className="w-full group-hover:bg-primary/10">
                    {t('Acceder')} →
                  </Button>
                </CardContent>
              </Card>
            </Link>
          </div>
        </div>

        {/* Actividad Reciente */}
        <Card className="border-l-2 border-l-primary/30">
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2">
              <div className="h-2 w-2 rounded-full bg-primary animate-pulse" />
              {t('Actividad Reciente')}
            </CardTitle>
            <CardDescription>{t('Últimas acciones administrativas')}</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {recent_users.slice(0, 5).map((user, idx) => (
                <div key={user.id} className="flex items-start gap-3 border-l-2 border-l-primary/30 pl-3 py-2 hover:bg-accent/50 rounded-r transition-colors">
                  <div className="flex-shrink-0">
                    <Badge variant="outline" className="text-xs">
                      {t('Usuario')}
                    </Badge>
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium">{user.name} {t('se registró')}</p>
                    <p className="text-xs text-muted-foreground truncate">{user.email}</p>
                  </div>
                  <span className="text-xs text-muted-foreground whitespace-nowrap">
                    {new Date(user.created_at).toLocaleDateString()}
                  </span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

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
