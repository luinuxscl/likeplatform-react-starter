import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router, useForm, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { useEffect, useMemo, useState } from 'react'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Alert } from '@/components/ui/alert'
import { Icon } from '@/components/ui/icon'
import { toast } from '@/components/ui/use-toast'
import { copyToClipboard } from '@/lib/copy-to-clipboard'

const INDEX_ROUTE = '/admin/api-keys'

type TokenUser = {
  id: number
  name: string
  email: string
}

type TokenCreator = {
  id: number
  name: string
  email: string
}

type TokenItem = {
  id: number
  name: string | null
  description: string | null
  abilities: string[] | null
  last_used_at: string | null
  last_used_ip: string | null
  last_used_user_agent: string | null
  expires_at: string | null
  created_at: string
  user: TokenUser | null
  created_by: TokenCreator | null
  self_managed: boolean
}

type PaginationLink = {
  url: string | null
  label: string
  active: boolean
}

type PageProps = {
  tokens: {
    data: TokenItem[]
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
    search: string
    perPage: number
    issuedBy: string
  }
  users: { id: number; name: string }[]
  generatedToken?: string | null
  allowedAbilities: string[]
}

export default function ApiKeysIndex() {
  const { t } = useI18n()
  const { props } = usePage<PageProps>()
  const { tokens, filters, users, generatedToken, allowedAbilities } = props
  const locale = (props as any)?.i18n?.locale ?? 'es'

  const [filterState, setFilterState] = useState({
    user: filters.user ?? null,
    search: filters.search ?? '',
    perPage: filters.perPage ?? 15,
    issuedBy: filters.issuedBy ?? '',
  })
  const [createDialogOpen, setCreateDialogOpen] = useState(false)
  const [revokeDialogTokenId, setRevokeDialogTokenId] = useState<number | null>(null)
  const [abilitiesText, setAbilitiesText] = useState('')

  const form = useForm({
    user_id: users[0]?.id?.toString() ?? '',
    name: '',
    description: '',
    abilities: [] as string[],
    expires_at: '',
  })

  useEffect(() => {
    const timeout = setTimeout(() => {
      router.get(
        INDEX_ROUTE,
        {
          user: filterState.user ?? '',
          search: filterState.search,
          perPage: filterState.perPage,
          issuedBy: filterState.issuedBy,
        },
        { preserveState: true, preserveScroll: true, replace: true }
      )
    }, 350)

    return () => clearTimeout(timeout)
  }, [filterState.user, filterState.search, filterState.perPage, filterState.issuedBy])

  useEffect(() => {
    if (!createDialogOpen) {
      form.reset()
      setAbilitiesText('')
    }
  }, [createDialogOpen])

  const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
    { title: t('Administración'), href: '/admin/dashboard' },
    { title: t('API Keys'), href: INDEX_ROUTE },
  ], [t])

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()

    const abilities = abilitiesText
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean)

    form.data.abilities = abilities

    form.post(INDEX_ROUTE, {
      preserveScroll: true,
      onSuccess: () => {
        setCreateDialogOpen(false)
      },
    })
  }

  const handleRevoke = (tokenId: number) => {
    router.delete(`${INDEX_ROUTE}/${tokenId}`, {
      preserveScroll: true,
      onFinish: () => setRevokeDialogTokenId(null),
    })
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={t('Administración - API Keys')} />
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="space-y-1">
            <div className="text-lg font-medium">{t('API Keys')}</div>
            <p className="text-sm text-muted-foreground">
              {t('Gestiona las API keys utilizadas para integraciones externas.')}
            </p>
            <p className="text-xs text-muted-foreground">
              {t('Habilidades disponibles para usuarios:')} {allowedAbilities.length ? allowedAbilities.join(', ') : '*'}
            </p>
          </div>

          <Dialog open={createDialogOpen} onOpenChange={setCreateDialogOpen}>
            <DialogTrigger asChild>
              <Button>{t('Nueva API Key')}</Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-lg">
              <DialogHeader>
                <DialogTitle>{t('Generar nueva API Key')}</DialogTitle>
              </DialogHeader>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-2">
                  <label className="text-sm font-medium text-foreground" htmlFor="api-user">
                    {t('Usuario')}
                  </label>
                  <select
                    id="api-user"
                    className="h-9 w-full rounded-md border border-input bg-background px-2 text-sm"
                    value={form.data.user_id}
                    onChange={(event) => form.setData('user_id', event.target.value)}
                  >
                    {users.map((user) => (
                      <option key={user.id} value={user.id}>
                        {user.name}
                      </option>
                    ))}
                  </select>
                  {form.errors.user_id && (
                    <p className="text-xs text-destructive">{form.errors.user_id}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium text-foreground" htmlFor="api-name">
                    {t('Nombre interno')}
                  </label>
                  <Input
                    id="api-name"
                    value={form.data.name}
                    onChange={(event) => form.setData('name', event.target.value)}
                    placeholder={t('Ej: Integración Zapier')}
                  />
                  {form.errors.name && <p className="text-xs text-destructive">{form.errors.name}</p>}
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium text-foreground" htmlFor="api-description">
                    {t('Descripción')}
                  </label>
                  <Textarea
                    id="api-description"
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    placeholder={t('Describe el uso de esta API key')}
                    rows={3}
                  />
                  {form.errors.description && (
                    <p className="text-xs text-destructive">{form.errors.description}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium text-foreground" htmlFor="api-abilities">
                    {t('Habilidades (opcional)')}
                  </label>
                  <Input
                    id="api-abilities"
                    value={abilitiesText}
                    onChange={(event) => setAbilitiesText(event.target.value)}
                    placeholder={t('Ej: read,write,subscriptions')}
                  />
                  <p className="text-xs text-muted-foreground">
                    {t('Separa las habilidades con comas. Si se deja vacío, se asignará acceso total ("*").')}
                  </p>
                  {form.errors.abilities && (
                    <p className="text-xs text-destructive">{form.errors.abilities}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium text-foreground" htmlFor="api-expires">
                    {t('Expira el')}
                  </label>
                  <Input
                    id="api-expires"
                    type="datetime-local"
                    value={form.data.expires_at}
                    onChange={(event) => form.setData('expires_at', event.target.value)}
                  />
                  {form.errors.expires_at && (
                    <p className="text-xs text-destructive">{form.errors.expires_at}</p>
                  )}
                </div>

                <DialogFooter>
                  <Button type="button" variant="outline" onClick={() => setCreateDialogOpen(false)}>
                    {t('Cancelar')}
                  </Button>
                  <Button type="submit" disabled={form.processing}>
                    {form.processing ? t('Creando...') : t('Crear API Key')}
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>

        {generatedToken ? (
          <Alert className="flex items-center justify-between gap-3">
            <div>
              <div className="font-medium">{t('API Key generada')}</div>
              <p className="text-sm text-muted-foreground">
                {t('Cópiala ahora, no se mostrará nuevamente.')} <span className="font-mono">{generatedToken}</span>
              </p>
            </div>
            <Button
              variant="outline"
              size="sm"
              onClick={async () => {
                const success = await copyToClipboard(generatedToken)

                if (success) {
                  toast({ title: t('Copiado'), description: t('La API key se ha copiado al portapapeles.') })
                } else {
                  toast({
                    title: t('No se pudo copiar'),
                    description: t('Intenta copiarla manualmente.'),
                    variant: 'destructive',
                  })
                }
              }}
            >
              {t('Copiar')}
            </Button>
          </Alert>
        ) : null}

        <Card>
          <CardContent className="flex flex-col gap-3 pt-4">
            <div className="grid gap-3 md:grid-cols-5">
              <div className="md:col-span-1">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Usuario')}</label>
                <select
                  className="h-9 w-full rounded-md border border-input bg-background px-2 text-sm"
                  value={filterState.user ?? ''}
                  onChange={(event) =>
                    setFilterState((prev) => ({
                      ...prev,
                      user: event.target.value ? Number(event.target.value) : null,
                    }))
                  }
                >
                  <option value="">{t('Todos')}</option>
                  {users.map((user) => (
                    <option key={user.id} value={user.id}>
                      {user.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="md:col-span-2">
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Buscar')}</label>
                <Input
                  value={filterState.search}
                  onChange={(event) => setFilterState((prev) => ({ ...prev, search: event.target.value }))}
                  placeholder={t('Nombre, descripción...')}
                />
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Emitida por')}</label>
                <select
                  className="h-9 w-full rounded-md border border-input bg-background px-2 text-sm"
                  value={filterState.issuedBy}
                  onChange={(event) => setFilterState((prev) => ({ ...prev, issuedBy: event.target.value }))}
                >
                  <option value="">{t('Todos')}</option>
                  <option value="admin">{t('Administrador')}</option>
                  <option value="self">{t('Usuario (autogestión)')}</option>
                </select>
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-muted-foreground">{t('Registros por página')}</label>
                <select
                  className="h-9 w-full rounded-md border border-input bg-background px-2 text-sm"
                  value={filterState.perPage}
                  onChange={(event) => setFilterState((prev) => ({ ...prev, perPage: Number(event.target.value) }))}
                >
                  {[10, 15, 25, 50].map((option) => (
                    <option key={option} value={option}>
                      {option}
                    </option>
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
                  <th className="px-4 py-3">{t('Nombre')}</th>
                  <th className="px-4 py-3">{t('Habilidades')}</th>
                  <th className="px-4 py-3">{t('Último uso')}</th>
                  <th className="px-4 py-3">{t('Expira')}</th>
                  <th className="px-4 py-3">{t('Creado por')}</th>
                  <th className="px-4 py-3 text-right">{t('Acciones')}</th>
                </tr>
              </thead>
              <tbody>
                {tokens.data.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="px-4 py-6 text-center text-muted-foreground">
                      {t('No hay API keys para los filtros seleccionados.')}
                    </td>
                  </tr>
                ) : null}

                {tokens.data.map((token) => (
                  <TokenRow
                    key={token.id}
                    token={token}
                    locale={locale}
                    t={t}
                    allowedAbilities={allowedAbilities}
                    onRevoke={() => setRevokeDialogTokenId(token.id)}
                  />
                ))}
              </tbody>
            </table>
          </div>
          <div className="flex items-center justify-between gap-2 border-t border-border p-3 text-sm">
            <div className="text-muted-foreground">
              {(tokens.from ?? 0)}-{(tokens.to ?? 0)} / {tokens.total}
            </div>
            <div className="flex flex-wrap items-center gap-1">
              {tokens.links.map((link, index) => (
                <Link
                  key={`${link.label}-${index}`}
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

      <Dialog open={revokeDialogTokenId !== null} onOpenChange={(open) => !open && setRevokeDialogTokenId(null)}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>{t('Revocar API Key')}</DialogTitle>
          </DialogHeader>
          <p className="text-sm text-muted-foreground">
            {t('¿Confirmas que deseas revocar esta API key? La integración dejará de tener acceso inmediatamente.')}
          </p>
          <DialogFooter>
            <Button variant="outline" onClick={() => setRevokeDialogTokenId(null)}>
              {t('Cancelar')}
            </Button>
            <Button
              variant="destructive"
              onClick={() => revokeDialogTokenId !== null && handleRevoke(revokeDialogTokenId)}
            >
              {t('Revocar')}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </AppLayout>
  )
}

type TokenRowProps = {
  token: TokenItem
  locale: string
  t: ReturnType<typeof useI18n>['t']
  allowedAbilities: string[]
  onRevoke: () => void
}

function TokenRow({ token, locale, t, allowedAbilities, onRevoke }: TokenRowProps) {
  const formatDate = (value: string | null) => {
    if (!value) return '—'
    try {
      return new Date(value).toLocaleString(locale)
    } catch (error) {
      return value
    }
  }

  return (
    <tr className="border-t border-border">
      <td className="px-4 py-3 align-top text-sm">
        {token.user ? (
          <div className="flex flex-col">
            <span className="font-medium">{token.user.name}</span>
            <span className="text-xs text-muted-foreground">{token.user.email}</span>
          </div>
        ) : (
          <span className="text-xs text-muted-foreground">{t('N/A')}</span>
        )}
      </td>
      <td className="px-4 py-3 align-top text-sm">
        <div className="flex flex-col gap-1">
          <span className="font-medium">{token.name ?? t('Sin nombre')}</span>
          {token.description ? (
            <span className="text-xs text-muted-foreground">{token.description}</span>
          ) : null}
          <span className="text-xs text-muted-foreground">
            {t('Creada')}: {formatDate(token.created_at)}
          </span>
        </div>
      </td>
      <td className="px-4 py-3 align-top text-sm">
        <div className="flex flex-wrap gap-1">
          {(token.abilities ?? ['*']).map((ability) => (
            <Badge key={ability} variant="secondary">
              {ability}
            </Badge>
          ))}
          {token.abilities === null && (
            <span className="text-xs text-muted-foreground">
              {t('Acceso total')}
            </span>
          )}
        </div>
        {token.abilities && token.abilities.some((ability) => !allowedAbilities.includes(ability)) && (
          <span className="mt-1 block text-xs text-muted-foreground">
            {t('Habilidades fuera de la lista blanca actual.')}
          </span>
        )}
      </td>
      <td className="px-4 py-3 align-top text-sm">
        <div className="flex flex-col gap-1">
          <span>{formatDate(token.last_used_at)}</span>
          {token.last_used_ip ? (
            <span className="text-xs text-muted-foreground">IP: {token.last_used_ip}</span>
          ) : null}
          {token.last_used_user_agent ? (
            <span className="text-xs text-muted-foreground line-clamp-2">
              UA: {token.last_used_user_agent}
            </span>
          ) : null}
        </div>
      </td>
      <td className="px-4 py-3 align-top text-sm">
        {formatDate(token.expires_at)}
      </td>
      <td className="px-4 py-3 align-top text-sm">
        {token.self_managed ? (
          <span className="text-xs text-muted-foreground">{t('Autogestionada')}</span>
        ) : token.created_by ? (
          <div className="flex flex-col">
            <span className="font-medium">{token.created_by.name}</span>
            <span className="text-xs text-muted-foreground">{token.created_by.email}</span>
          </div>
        ) : (
          <span className="text-xs text-muted-foreground">—</span>
        )}
      </td>
      <td className="px-4 py-3 text-right">
        <div className="flex items-center justify-end gap-2">
          <Button variant="ghost" size="sm" onClick={onRevoke}>
            <Icon name="trash-2" className="h-4 w-4" />
            <span className="sr-only">{t('Revocar')}</span>
          </Button>
        </div>
      </td>
    </tr>
  )
}
