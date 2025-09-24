import AppLayout from '@/layouts/app-layout'
import SettingsLayout from '@/layouts/settings/layout'
import { Head, Link, router, useForm, usePage } from '@inertiajs/react'
import { useEffect, useMemo, useState } from 'react'
import { type BreadcrumbItem } from '@/types'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Alert } from '@/components/ui/alert'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import { Trash2 } from 'lucide-react'
import { toast } from '@/components/ui/use-toast'
import { copyToClipboard } from '@/lib/copy-to-clipboard'

const INDEX_ROUTE = '/settings/api-keys'

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
  allowedAbilities: string[]
  generatedToken?: string | null
}

export default function SettingsApiKeysIndex() {
  const { t } = useI18n()
  const { props } = usePage<PageProps>()
  const { tokens, allowedAbilities, generatedToken } = props
  const locale = (props as any)?.i18n?.locale ?? 'es'

  const abilityOptions = useMemo(() => (allowedAbilities.length ? allowedAbilities : ['read']), [allowedAbilities])

  const form = useForm({
    name: '',
    description: '',
    abilities: [...abilityOptions],
    expires_at: '',
  })

  const [creating, setCreating] = useState(false)

  useEffect(() => {
    const normalized = form.data.abilities.filter((ability: string) => abilityOptions.includes(ability))
    form.setData('abilities', normalized.length ? normalized : [...abilityOptions])
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [abilityOptions])

  const breadcrumbs: BreadcrumbItem[] = useMemo(
    () => [
      { title: t('Configuración'), href: '/settings/profile' },
      { title: t('API Keys'), href: INDEX_ROUTE },
    ],
    [t],
  )

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setCreating(true)

    form.post(INDEX_ROUTE, {
      preserveScroll: true,
      onFinish: () => setCreating(false),
      onSuccess: () => {
        form.reset()
        form.setData('abilities', [...abilityOptions])
      },
    })
  }

  const toggleAbility = (ability: string, checked: boolean | 'indeterminate') => {
    form.setData(
      'abilities',
      checked === true
        ? Array.from(new Set([...form.data.abilities, ability]))
        : form.data.abilities.filter((value: string) => value !== ability),
    )
  }

  const handleDelete = (tokenId: number) => {
    router.delete(`${INDEX_ROUTE}/${tokenId}`, {
      preserveScroll: true,
    })
  }

  const formatDate = (value: string | null) => {
    if (!value) return '—'

    try {
      return new Date(value).toLocaleString(locale)
    } catch (error) {
      return value
    }
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={t('Configuración - API Keys')} />
      <SettingsLayout>
        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="text-base font-semibold">{t('Generar nueva API Key')}</CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-2">
                  <label className="text-sm font-medium text-foreground" htmlFor="api-name">
                    {t('Nombre interno')}
                  </label>
                  <Input
                    id="api-name"
                    value={form.data.name}
                    onChange={(event) => form.setData('name', event.target.value)}
                    placeholder={t('Ej: Integración personal')}
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
                    placeholder={t('Describe el uso previsto de esta API key')}
                    rows={3}
                  />
                  {form.errors.description && <p className="text-xs text-destructive">{form.errors.description}</p>}
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium text-foreground">{t('Habilidades permitidas')}</label>
                  <p className="text-xs text-muted-foreground">
                    {t('Selecciona las habilidades que esta API key podrá usar. Por defecto se habilitan todas las disponibles.')}
                  </p>
                  <div className="flex flex-wrap gap-3">
                    {abilityOptions.map((ability: string) => (
                      <label key={ability} className="flex items-center gap-2 text-sm text-foreground">
                        <Checkbox
                          checked={form.data.abilities.includes(ability)}
                          onCheckedChange={(checked) => toggleAbility(ability, checked)}
                        />
                        <span>{ability}</span>
                      </label>
                    ))}
                  </div>
                  {form.errors.abilities && <p className="text-xs text-destructive">{form.errors.abilities}</p>}
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium text-foreground" htmlFor="api-expires">
                    {t('Expira el (opcional)')}
                  </label>
                  <Input
                    id="api-expires"
                    type="datetime-local"
                    value={form.data.expires_at}
                    onChange={(event) => form.setData('expires_at', event.target.value)}
                  />
                  {form.errors.expires_at && <p className="text-xs text-destructive">{form.errors.expires_at}</p>}
                </div>

                <div className="flex items-center gap-3">
                  <Button type="submit" disabled={creating || form.processing}>
                    {form.processing ? t('Creando...') : t('Crear API Key')}
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => {
                      form.reset()
                      form.setData('abilities', [...abilityOptions])
                    }}
                  >
                    {t('Limpiar formulario')}
                  </Button>
                </div>
              </form>
            </CardContent>
          </Card>

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
            <CardHeader>
              <CardTitle className="text-base font-semibold">{t('Mis API Keys')}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="relative overflow-hidden rounded-xl border border-border">
                <div className="relative w-full overflow-x-auto">
                  <table className="w-full text-left text-sm">
                    <thead className="bg-muted text-xs uppercase">
                      <tr>
                        <th className="px-4 py-3">{t('Nombre')}</th>
                        <th className="px-4 py-3">{t('Habilidades')}</th>
                        <th className="px-4 py-3">{t('Último uso')}</th>
                        <th className="px-4 py-3">{t('Expira')}</th>
                        <th className="px-4 py-3 text-right">{t('Acciones')}</th>
                      </tr>
                    </thead>
                    <tbody>
                      {tokens.data.length === 0 ? (
                        <tr>
                          <td colSpan={5} className="px-4 py-6 text-center text-muted-foreground">
                            {t('Aún no has generado API keys.')}
                          </td>
                        </tr>
                      ) : null}

                      {tokens.data.map((token) => (
                        <tr key={token.id} className="border-t border-border">
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
                              {(token.abilities ?? abilityOptions).map((ability: string) => (
                                <Badge key={ability} variant="secondary">
                                  {ability}
                                </Badge>
                              ))}
                            </div>
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
                          <td className="px-4 py-3 text-right">
                            <div className="flex items-center justify-end gap-2">
                              <Button variant="destructive" size="sm" onClick={() => handleDelete(token.id)}>
                                <Trash2 className="h-4 w-4" />
                                <span className="sr-only">{t('Revocar')}</span>
                              </Button>
                            </div>
                          </td>
                        </tr>
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
                          'rounded-md px-2 py-1 text-sm transition-colors',
                          link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted',
                          !link.url ? 'pointer-events-none opacity-50' : '',
                        ].join(' ')}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                      />
                    ))}
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </SettingsLayout>
    </AppLayout>
  )
}
