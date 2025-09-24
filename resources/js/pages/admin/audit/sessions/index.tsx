import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useEffect, useMemo, useState } from 'react'

import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'

const SESSIONS_ROUTE = '/admin/audit/sessions'
const LOGS_ROUTE = '/admin/audit/logs'

type SessionUser = {
  id: number
  name: string
  email: string
}

type SessionItem = {
  id: number
  session_id: string
  user: SessionUser | null
  ip_address: string | null
  user_agent: string | null
  device: string | null
  platform: string | null
  browser: string | null
  login_at: string | null
  last_activity_at: string | null
  logout_at: string | null
}

type PaginationLink = {
  url: string | null
  label: string
  active: boolean
}

type SessionsPageProps = {
  sessions: {
    data: SessionItem[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
  filters: {
    user: number | null
    active: boolean
    search: string
    perPage: number
  }
  options: {
    users: { id: number; name: string }[]
  }
}

const formatDateTime = (value: string | null, locale: string) => {
  if (!value) return '—'
  try {
    return new Date(value).toLocaleString(locale)
  } catch (error) {
    return value
  }
}

export default function AuditSessionsIndex() {
  const { t } = useI18n()
  const page = usePage<{ props: SessionsPageProps }>()
  const { sessions, filters, options } = page.props as unknown as SessionsPageProps
  const locale = (page.props as any)?.i18n?.locale ?? 'es'

  const [internalFilters, setInternalFilters] = useState({
    user: filters.user ?? null,
    active: filters.active ?? false,
    search: filters.search ?? '',
    perPage: filters.perPage ?? 15,
  })

  useEffect(() => {
    const id = setTimeout(() => {
      router.get(
        SESSIONS_ROUTE,
        {
          ...internalFilters,
        },
        { preserveState: true, preserveScroll: true, replace: true }
      )
    }, 350)

    return () => clearTimeout(id)
  }, [internalFilters.user, internalFilters.active, internalFilters.search, internalFilters.perPage])

  const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
    { title: t('Administración'), href: '/admin/dashboard' },
    { title: t('Auditoría'), href: LOGS_ROUTE },
    { title: t('Sesiones'), href: SESSIONS_ROUTE },
  ], [t])

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={t('Auditoría - Sesiones')} />
      <div className="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="space-y-1">
            <div className="text-lg font-medium">{t('Sesiones de usuarios')}</div>
            <p className="text-sm text-muted-foreground">
              {t('Sesiones activas y recientes con información de dispositivo, IP y actividad.')}
            </p>
          </div>
          <Link href={LOGS_ROUTE}>
            <Button variant="outline">{t('Ver registros de auditoría')}</Button>
          </Link>
        </div>

        <Card>
          <CardContent className="flex flex-col gap-3 pt-4">
            <div className="grid gap-3 md:grid-cols-4">
              <div className="md:col-span-1">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Usuario')}</label>
                <select
                  className="h-9 w-full rounded-md border border-input bg-background px-2 text-sm"
                  value={internalFilters.user ?? ''}
                  onChange={(e) => setInternalFilters((prev) => ({ ...prev, user: e.target.value ? Number(e.target.value) : null }))}
                >
                  <option value="">{t('Todos')}</option>
                  {options.users.map((user) => (
                    <option key={user.id} value={user.id}>{user.name}</option>
                  ))}
                </select>
              </div>

              <div className="md:col-span-1">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Estado')}</label>
                <select
                  className="h-9 w-full rounded-md border border-input bg-background px-2 text-sm"
                  value={internalFilters.active ? '1' : '0'}
                  onChange={(e) => setInternalFilters((prev) => ({ ...prev, active: e.target.value === '1' }))}
                >
                  <option value="0">{t('Todas')}</option>
                  <option value="1">{t('Activas')}</option>
                </select>
              </div>

              <div className="md:col-span-2">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Buscar (IP, dispositivo, navegador)')}</label>
                <Input
                  placeholder={t('Buscar...')}
                  value={internalFilters.search}
                  onChange={(e) => setInternalFilters((prev) => ({ ...prev, search: e.target.value }))}
                />
              </div>
            </div>

            <div className="grid gap-3 md:grid-cols-4">
              <div className="md:col-span-1">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Resultados por página')}</label>
                <select
                  className="h-9 w-full rounded-md border border-input bg-background px-2 text-sm"
                  value={internalFilters.perPage}
                  onChange={(e) => setInternalFilters((prev) => ({ ...prev, perPage: Number(e.target.value) }))}
                >
                  {[10, 15, 25, 50].map((n) => (
                    <option key={n} value={n}>{n}</option>
                  ))}
                </select>
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="relative overflow-hidden rounded-xl border border-border">
          <div className="relative w-full overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-muted text-xs uppercase">
                <tr>
                  <th className="px-4 py-3">{t('Usuario')}</th>
                  <th className="px-4 py-3">{t('Dispositivo')}</th>
                  <th className="px-4 py-3">{t('Login')}</th>
                  <th className="px-4 py-3">{t('Última actividad')}</th>
                  <th className="px-4 py-3">{t('Estado')}</th>
                  <th className="px-4 py-3">{t('IP')}</th>
                  <th className="px-4 py-3 text-right">{t('Detalles')}</th>
                </tr>
              </thead>
              <tbody>
                {sessions.data.length === 0 && (
                  <tr>
                    <td colSpan={7} className="px-4 py-6 text-center text-muted-foreground">
                      {t('No hay sesiones para los filtros seleccionados.')}
                    </td>
                  </tr>
                )}

                {sessions.data.map((session) => (
                  <SessionRow key={session.id} session={session} locale={locale} t={t} />
                ))}
              </tbody>
            </table>
          </div>
          <div className="flex items-center justify之间 gap-2 border-t border-border p-3 text-sm">
            <div className="text-muted-foreground">
              {(sessions.from ?? 0)}-{(sessions.to ?? 0)} / {sessions.total}
            </div>
            <div className="flex flex-wrap items-center gap-1">
              {sessions.links.map((link, idx) => (
                <Link
                  key={`${link.label}-${idx}`}
                  href={link.url ?? '#'}
                  preserveState
                  preserveScroll
                  className={[
                    'rounded-md px-2 py-1 text-sm',
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

type SessionRowProps = {
  session: SessionItem
  locale: string
  t: ReturnType<typeof useI18n>['t']
}

function SessionRow({ session, locale, t }: SessionRowProps) {
  const [open, setOpen] = useState(false)
  const isActive = !session.logout_at

  return (
    <>
      <tr className="border-t border-border">
        <td className="px-4 py-3 align-top text-sm">
          {session.user ? (
            <div className="flex flex-col">
              <span className="font-medium">{session.user.name}</span>
              <span className="text-xs text-muted-foreground">{session.user.email}</span>
              <span className="text-xs text-muted-foreground">ID: {session.session_id}</span>
            </div>
          ) : (
            <span className="text-xs text-muted-foreground">{t('Desconocido')}</span>
          )}
        </td>
        <td className="px-4 py-3 align-top text-sm">
          <div className="flex flex-col">
            <span>{session.device ?? '—'}</span>
            <span className="text-xs text-muted-foreground">
              {session.platform ?? '—'} · {session.browser ?? '—'}
            </span>
          </div>
        </td>
        <td className="px-4 py-3 align-top text-xs text-muted-foreground">
          {formatDateTime(session.login_at, locale)}
        </td>
        <td className="px-4 py-3 align-top text-xs text-muted-foreground">
          {formatDateTime(session.last_activity_at, locale)}
        </td>
        <td className="px-4 py-3 align-top text-sm">
          <Badge variant={isActive ? 'default' : 'outline'}>
            {isActive ? t('Activa') : t('Cerrada')}
          </Badge>
        </td>
        <td className="px-4 py-3 align-top text-sm">
          <div className="flex flex-col">
            <span>{session.ip_address ?? '—'}</span>
            {session.user_agent && <span className="text-xs text-muted-foreground line-clamp-2">{session.user_agent}</span>}
          </div>
        </td>
        <td className="px-4 py-3 text-right">
          <Button variant="ghost" size="sm" onClick={() => setOpen((prev) => !prev)}>
            {open ? t('Ocultar') : t('Ver detalles')}
          </Button>
        </td>
      </tr>
      {open && (
        <tr className="border-t border-border bg-muted/40">
          <td colSpan={7} className="px-4 py-4">
            <div className="grid gap-3 md:grid-cols-2">
              <DetailBlock title={t('Información técnica')}>
                <InfoLine label={t('IP')} value={session.ip_address ?? '—'} />
                <InfoLine label={t('User Agent')} value={session.user_agent ?? '—'} />
              </DetailBlock>
              <DetailBlock title={t('Tiempos')}>
                <InfoLine label={t('Login')} value={formatDateTime(session.login_at, locale)} />
                <InfoLine label={t('Última actividad')} value={formatDateTime(session.last_activity_at, locale)} />
                <InfoLine label={t('Logout')} value={formatDateTime(session.logout_at, locale)} />
              </DetailBlock>
            </div>
          </td>
        </tr>
      )}
    </>
  )
}

type DetailBlockProps = {
  title: string
  children: React.ReactNode
}

function DetailBlock({ title, children }: DetailBlockProps) {
  return (
    <div className="rounded-lg border border-border bg-card p-3">
      <div className="text-xs font-semibold uppercase text-muted-foreground">{title}</div>
      <div className="mt-2 space-y-1 text-xs text-foreground">
        {children}
      </div>
    </div>
  )
}

type InfoLineProps = {
  label: string
  value: string
}

function InfoLine({ label, value }: InfoLineProps) {
  return (
    <div className="flex items-center justify-between gap-2">
      <span className="text-muted-foreground">{label}</span>
      <span className="font-medium text-foreground">{value}</span>
    </div>
  )
}
