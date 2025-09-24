import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useEffect, useMemo, useState } from 'react'

import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'

type LogUser = {
  id: number
  name: string
  email: string
}

type LogItem = {
  id: number
  action: string
  model: string | null
  model_full: string | null
  model_id: number | null
  user: LogUser | null
  old_values: Record<string, unknown> | null
  new_values: Record<string, unknown> | null
  metadata: Record<string, unknown> | null
  ip_address: string | null
  user_agent: string | null
  url: string | null
  created_at: string
}

type PaginationLink = {
  url: string | null
  label: string
  active: boolean
}

type LogsPageProps = {
  logs: {
    data: LogItem[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
  filters: {
    action: string
    model: string
    user: number | null
    date_from: string
    date_to: string
    search: string
    perPage: number
  }
  options: {
    actions: string[]
    models: { value: string; label: string }[]
    users: { id: number; name: string }[]
  }
}

const formatDateTime = (value: string, locale: string) => {
  try {
    return new Date(value).toLocaleString(locale)
  } catch (error) {
    return value
  }
}

export default function AuditLogsIndex() {
  const { t } = useI18n()
  const page = usePage<{ props: LogsPageProps }>()
  const { logs, filters, options } = page.props as unknown as LogsPageProps
  const locale = (page.props as any)?.i18n?.locale ?? 'es'

  const [internalFilters, setInternalFilters] = useState({
    action: filters.action ?? '',
    model: filters.model ?? '',
    user: filters.user ?? null,
    date_from: filters.date_from ?? '',
    date_to: filters.date_to ?? '',
    search: filters.search ?? '',
    perPage: filters.perPage ?? 15,
  })

  useEffect(() => {
    const id = setTimeout(() => {
      router.get(
        LOGS_ROUTE,
        {
          ...internalFilters,
        },
        { preserveState: true, preserveScroll: true, replace: true }
      )
    }, 350)

    return () => clearTimeout(id)
  }, [internalFilters.action, internalFilters.model, internalFilters.user, internalFilters.date_from, internalFilters.date_to, internalFilters.search, internalFilters.perPage])

  const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
    { title: t('Administración'), href: '/admin/dashboard' },
    { title: t('Auditoría'), href: LOGS_ROUTE },
    { title: t('Registros'), href: LOGS_ROUTE },
  ], [t])

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={t('Auditoría - Registros')} />
      <div className="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="space-y-1">
            <div className="text-lg font-medium">{t('Registros de auditoría')}</div>
            <p className="text-sm text-muted-foreground">
              {t('Seguimiento de acciones realizadas en los modelos auditados.')}
            </p>
          </div>
          <Link href={SESSIONS_ROUTE}>
            <Button variant="outline">{t('Ver sesiones de usuarios')}</Button>
          </Link>
        </div>

        <Card>
          <CardContent className="flex flex-col gap-3 pt-4">
            <div className="grid gap-3 md:grid-cols-5">
              <div className="md:col-span-1">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Acción')}</label>
                <select
                  className="h-9 w-full rounded-md border border-input bg-background px-2 text-sm"
                  value={internalFilters.action}
                  onChange={(e) => setInternalFilters((prev) => ({ ...prev, action: e.target.value }))}
                >
                  <option value="">{t('Todas')}</option>
                  {options.actions.map((action) => (
                    <option key={action} value={action}>{action}</option>
                  ))}
                </select>
              </div>

              <div className="md:col-span-1">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Modelo')}</label>
                <select
                  className="h-9 w-full rounded-md border border-input bg-background px-2 text-sm"
                  value={internalFilters.model}
                  onChange={(e) => setInternalFilters((prev) => ({ ...prev, model: e.target.value }))}
                >
                  <option value="">{t('Todos')}</option>
                  {options.models.map((model) => (
                    <option key={model.value} value={model.value}>{model.label}</option>
                  ))}
                </select>
              </div>

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
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Desde')}</label>
                <Input
                  type="date"
                  value={internalFilters.date_from}
                  onChange={(e) => setInternalFilters((prev) => ({ ...prev, date_from: e.target.value }))}
                />
              </div>

              <div className="md:col-span-1">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Hasta')}</label>
                <Input
                  type="date"
                  value={internalFilters.date_to}
                  onChange={(e) => setInternalFilters((prev) => ({ ...prev, date_to: e.target.value }))}
                />
              </div>
            </div>

            <div className="grid gap-3 md:grid-cols-3">
              <div className="md:col-span-2">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Buscar (URL, IP, User Agent)')}</label>
                <Input
                  placeholder={t('Buscar...')}
                  value={internalFilters.search}
                  onChange={(e) => setInternalFilters((prev) => ({ ...prev, search: e.target.value }))}
                />
              </div>
              <div>
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
                  <th className="px-4 py-3">{t('Fecha')}</th>
                  <th className="px-4 py-3">{t('Acción')}</th>
                  <th className="px-4 py-3">{t('Modelo')}</th>
                  <th className="px-4 py-3">{t('Usuario')}</th>
                  <th className="px-4 py-3">{t('IP')}</th>
                  <th className="px-4 py-3">{t('URL')}</th>
                  <th className="px-4 py-3 text-right">{t('Detalles')}</th>
                </tr>
              </thead>
              <tbody>
                {logs.data.length === 0 && (
                  <tr>
                    <td colSpan={7} className="px-4 py-6 text-center text-muted-foreground">
                      {t('No hay registros de auditoría para los filtros seleccionados.')}
                    </td>
                  </tr>
                )}

                {logs.data.map((log) => (
                  <LogRow key={log.id} log={log} locale={locale} t={t} />
                ))}
              </tbody>
            </table>
          </div>
          <div className="flex items-center justify-between gap-2 border-t border-border p-3 text-sm">
            <div className="text-muted-foreground">
              {(logs.from ?? 0)}-{(logs.to ?? 0)} / {logs.total}
            </div>
            <div className="flex flex-wrap items-center gap-1">
              {logs.links.map((link, idx) => (
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

type LogRowProps = {
  log: LogItem
  locale: string
  t: ReturnType<typeof useI18n>['t']
}

const LOGS_ROUTE = '/admin/audit/logs'
const SESSIONS_ROUTE = '/admin/audit/sessions'

function LogRow({ log, locale, t }: LogRowProps) {
  const [open, setOpen] = useState(false)

  return (
    <>
      <tr className="border-t border-border">
        <td className="px-4 py-3 align-top text-xs text-muted-foreground">
          {formatDateTime(log.created_at, locale)}
        </td>
        <td className="px-4 py-3 align-top">
          <Badge variant="secondary" className="capitalize">
            {log.action}
          </Badge>
        </td>
        <td className="px-4 py-3 align-top text-sm">
          <div className="flex flex-col">
            <span className="font-medium">{log.model ?? t('N/A')}</span>
            {log.model_full && <span className="text-xs text-muted-foreground">{log.model_full}</span>}
            {log.model_id && <span className="text-xs text-muted-foreground">ID: {log.model_id}</span>}
          </div>
        </td>
        <td className="px-4 py-3 align-top text-sm">
          {log.user ? (
            <div className="flex flex-col">
              <span>{log.user.name}</span>
              <span className="text-xs text-muted-foreground">{log.user.email}</span>
            </div>
          ) : (
            <span className="text-xs text-muted-foreground">{t('Sistema')}</span>
          )}
        </td>
        <td className="px-4 py-3 align-top text-sm">
          <div className="flex flex-col">
            <span>{log.ip_address ?? '—'}</span>
            {log.user_agent && <span className="text-xs text-muted-foreground line-clamp-2">{log.user_agent}</span>}
          </div>
        </td>
        <td className="px-4 py-3 align-top text-sm">
          {log.url ? (
            <a href={log.url} target="_blank" rel="noreferrer" className="text-primary hover:underline">
              {log.url}
            </a>
          ) : (
            <span className="text-xs text-muted-foreground">{t('N/A')}</span>
          )}
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
              <DetailBlock title={t('Valores anteriores')} data={log.old_values} />
              <DetailBlock title={t('Nuevos valores')} data={log.new_values} />
              <DetailBlock title={t('Metadatos')} data={log.metadata} />
            </div>
          </td>
        </tr>
      )}
    </>
  )
}

type DetailBlockProps = {
  title: string
  data: Record<string, unknown> | null
}

function DetailBlock({ title, data }: DetailBlockProps) {
  return (
    <div className="rounded-lg border border-border bg-card p-3">
      <div className="text-xs font-semibold uppercase text-muted-foreground">{title}</div>
      {data && Object.keys(data).length > 0 ? (
        <pre className="mt-2 max-h-56 overflow-auto rounded-md bg-muted/60 p-2 text-xs text-foreground">
          {JSON.stringify(data, null, 2)}
        </pre>
      ) : (
        <p className="mt-2 text-xs text-muted-foreground">—</p>
      )}
    </div>
  )
}
