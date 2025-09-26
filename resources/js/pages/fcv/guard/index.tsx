import React, { useEffect, useMemo, useState } from 'react'
import AppLayout from '@/layouts/app-layout'
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'

// Guardia UI (Inertia page name: "fcv/guard/index")
// Requisitos: búsqueda rápida por RUT/Nombre, una sola pantalla, alto contraste, fuente escalable

type Person = {
  id: number
  name: string
  rut: string
  status: string
  memberships: Array<{
    role: string
    organization: { id: number; name: string; access_rule_preset: string }
  }>
}

type VerifyResponse = {
  allowed: boolean
  status: 'permitido' | 'denegado'
  reason?: string
  person?: { id: number; rut: string; name: string }
  organization?: { id: number; name: string; access_rule_preset: string }
}

type RecentItem = {
  id: number
  occurred_at: string
  direction: 'entrada' | 'salida'
  status: 'permitido' | 'denegado'
  reason?: string | null
  person: { id: number; name: string; rut: string } | null
}

function classNames(...xs: Array<string | false | undefined>) {
  return xs.filter(Boolean).join(' ')
}

function normalizeRut(raw: string) {
  const only = (raw || '').replace(/[^0-9kK]/g, '')
  return only.toLowerCase()
}

function looksLikeRut(raw: string) {
  const s = normalizeRut(raw)
  return s.length >= 6 && /^(\d{6,8}[0-9k])$/.test(s)
}

function useDebounced<T>(value: T, delay = 250) {
  const [debounced, setDebounced] = useState(value)
  useEffect(() => {
    const t = setTimeout(() => setDebounced(value), delay)
    return () => clearTimeout(t)
  }, [value, delay])
  return debounced
}

const FONT_SIZES = [
  { label: 'M', value: 'text-base' },
  { label: 'L', value: 'text-lg' },
  { label: 'XL', value: 'text-xl' },
  { label: '2XL', value: 'text-2xl' },
]

export default function GuardDashboard() {
  const { t } = useI18n()
  const [query, setQuery] = useState('')
  const debouncedQuery = useDebounced(query)
  const [searching, setSearching] = useState(false)
  const [results, setResults] = useState<Person[]>([])

  const [verifyLoading, setVerifyLoading] = useState(false)
  const [verifyResult, setVerifyResult] = useState<VerifyResponse | null>(null)

  const [fontIdx, setFontIdx] = useState(1) // default L
  const fontClass = useMemo(() => FONT_SIZES[fontIdx]?.value ?? 'text-base', [fontIdx])

  const [highContrast, setHighContrast] = useState(false)
  const borderStrong = highContrast ? 'border-foreground' : 'border-border'
  const cardBg = highContrast ? 'bg-background' : 'bg-card'

  const [recent, setRecent] = useState<RecentItem[]>([])

  // Buscar por nombre o RUT
  useEffect(() => {
    let active = true
    async function run() {
      if (!debouncedQuery) {
        setResults([])
        return
      }
      setSearching(true)
      try {
        const term = looksLikeRut(debouncedQuery) ? normalizeRut(debouncedQuery) : debouncedQuery
        const url = `/fcv/search?q=${encodeURIComponent(term)}`
        const res = await fetch(url, {
          method: 'GET',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        if (!active) return
        if (res.ok) {
          const data = await res.json()
          setResults(data.results ?? [])
        } else {
          setResults([])
        }
      } catch {
        if (active) setResults([])
      } finally {
        if (active) setSearching(false)
      }
    }
    run()
    return () => {
      active = false
    }
  }, [debouncedQuery])

  // Accesos recientes (polling cada 10s)
  useEffect(() => {
    let active = true
    let timer: number | undefined
    async function fetchRecent() {
      try {
        const res = await fetch('/fcv/access/recent?limit=20', {
          method: 'GET',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        if (!active) return
        if (res.ok) {
          const data = await res.json()
          setRecent(data.items ?? [])
        }
      } catch {
        /* ignore */
      }
    }
    fetchRecent()
    timer = window.setInterval(fetchRecent, 10000)
    return () => {
      active = false
      if (timer) window.clearInterval(timer)
    }
  }, [])

  function getCsrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null
    return el?.content || ''
  }

  async function handleVerify(rut: string) {
    setVerifyLoading(true)
    setVerifyResult(null)
    try {
      const res = await fetch('/fcv/verify', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ rut }),
        credentials: 'same-origin',
      })
      if (res.ok) {
        const data: VerifyResponse = await res.json()
        setVerifyResult(data)
      }
    } finally {
      setVerifyLoading(false)
    }
  }

  async function handleAccess(direction: 'entrada' | 'salida', status: 'permitido' | 'denegado', rut?: string) {
    const res = await fetch('/fcv/access', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({ rut: rut ?? null, direction, status }),
      credentials: 'same-origin',
    })
    if (!res.ok) return
    // refrescar recientes al registrar
    try {
      const r = await fetch('/fcv/access/recent?limit=20', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      if (r.ok) {
        const data = await r.json()
        setRecent(data.items ?? [])
      }
    } catch {}
  }

  const breadcrumbs: BreadcrumbItem[] = [
    { title: t('FCV'), href: '/fcv/guard' },
    { title: t('Portería'), href: '/fcv/guard' },
  ]

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={t('Portería')} />
      <div className={classNames('flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4', fontClass)}>
        {/* Barra Superior: Búsqueda + Controles de tamaño + Alto contraste */}
        <div className="flex flex-col gap-3 md:flex-row md:items-center">
          <div className="flex-1">
            <label className="block text-sm text-muted-foreground mb-1">Buscar por nombre o RUT</label>
            <Input
              type="text"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              onKeyDown={(e) => {
                if (e.key === 'Enter' && e.shiftKey) {
                  // Shift+Enter = registrar entrada (permitido)
                  e.preventDefault()
                  const rut = looksLikeRut(query) ? normalizeRut(query) : undefined
                  void handleAccess('entrada', 'permitido', rut)
                } else if (e.key === 'Enter' && e.altKey) {
                  // Alt+Enter = registrar salida (permitido)
                  e.preventDefault()
                  const rut = looksLikeRut(query) ? normalizeRut(query) : undefined
                  void handleAccess('salida', 'permitido', rut)
                } else if (e.key === 'Enter') {
                  // Enter = verificar
                  e.preventDefault()
                  const rut = looksLikeRut(query) ? normalizeRut(query) : query
                  void handleVerify(rut)
                }
              }}
              placeholder="Ej: 12345678k o Juan Pérez"
              className={classNames('w-full', borderStrong)}
            />
          </div>
          <div className="flex items-center gap-2">
            <span className="text-sm text-muted-foreground">Tamaño</span>
            <div className={classNames('flex rounded-md border overflow-hidden', borderStrong)}>
              {FONT_SIZES.map((fs, i) => (
                <Button
                  key={fs.label}
                  variant={i === fontIdx ? 'secondary' : 'outline'}
                  className="px-3 py-2"
                  onClick={() => setFontIdx(i)}
                >
                  {fs.label}
                </Button>
              ))}
            </div>
            <Button
              variant={highContrast ? 'destructive' : 'outline'}
              className={classNames('px-3 py-2', borderStrong)}
              onClick={() => setHighContrast((v) => !v)}
              title="Alto contraste"
            >
              {highContrast ? 'Contraste: ON' : 'Contraste: OFF'}
            </Button>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
          {/* Columna 1: Resultados de búsqueda */}
          <section className={classNames('lg:col-span-2 rounded-lg border', borderStrong, cardBg)}>
            <div className={classNames('border-b p-3', borderStrong)}>
              <h2 className="font-semibold">Resultados</h2>
              {searching && <p className="text-sm text-muted-foreground">Buscando…</p>}
            </div>
            <div className={classNames('divide-y', borderStrong)}>
              {results.length === 0 && (
                <div className="p-4 text-sm text-muted-foreground">Sin resultados</div>
              )}
              {results.map((p) => (
                <article key={p.id} className="p-3 flex items-start justify-between gap-3">
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <span className="font-medium">{p.name}</span>
                      <Badge variant="secondary" className={classNames('text-xs', borderStrong)}> {p.rut} </Badge>
                    </div>
                    <div className="flex flex-wrap gap-2">
                      {p.memberships.map((m, idx) => (
                        <span key={idx} className="flex items-center gap-2">
                          <Badge variant="outline" className="text-xs capitalize">{m.role}</Badge>
                          <Badge
                            variant={m.organization.access_rule_preset === 'acceso_total' ? 'default' : m.organization.access_rule_preset === 'horario_flexible' ? 'secondary' : 'outline'}
                            className="text-xs"
                            title={`Regla: ${m.organization.access_rule_preset}`}
                          >
                            {m.organization.name}
                          </Badge>
                        </span>
                      ))}
                    </div>
                  </div>
                  <div className="flex shrink-0 items-center gap-2">
                    <Button
                      onClick={() => handleVerify(p.rut)}
                      className={classNames('', borderStrong)}
                    >
                      Verificar
                    </Button>
                    <div className="flex items-center gap-1">
                      <Button
                        variant="outline"
                        className={classNames('px-2 py-2', borderStrong)}
                        onClick={() => handleAccess('entrada', 'permitido', p.rut)}
                        title="Registrar entrada"
                      >
                        ⬅︎
                      </Button>
                      <Button
                        variant="outline"
                        className={classNames('px-2 py-2', borderStrong)}
                        onClick={() => handleAccess('salida', 'permitido', p.rut)}
                        title="Registrar salida"
                      >
                        ➝
                      </Button>
                    </div>
                  </div>
                </article>
              ))}
            </div>
          </section>

          {/* Columna 2: Panel de verificación / resultado + recientes */}
          <aside className={classNames('rounded-lg border p-3 space-y-3', borderStrong, cardBg)}>
            <h2 className="font-semibold">Verificación</h2>
            <div className="space-y-2">
              <Button
                className={classNames('w-full', borderStrong)}
                onClick={() => {
                  if (query) handleVerify(query)
                }}
              >
                {verifyLoading ? 'Verificando…' : 'Verificar RUT/NOMBRE actual'}
              </Button>
              {verifyResult && (
                <div
                  className={classNames(
                    'rounded-md border px-3 py-2',
                    verifyResult.allowed ? 'border-green-600' : 'border-red-600'
                  )}
                >
                  <div className="flex items-center justify-between">
                    <span className="font-medium">{verifyResult.status.toUpperCase()}</span>
                    {verifyResult.organization && (
                      <span className="text-xs text-muted-foreground">{verifyResult.organization.name}</span>
                    )}
                  </div>
                  <div className="text-sm text-muted-foreground mt-1">
                    {verifyResult.reason}
                  </div>
                  {verifyResult.person && (
                    <div className="mt-2 text-sm">
                      <div className="font-medium">{verifyResult.person.name}</div>
                      <div className="text-muted-foreground">{verifyResult.person.rut}</div>
                    </div>
                  )}
                  <div className="mt-3 flex items-center gap-2">
                    <Button
                      variant="outline"
                      className={classNames('', borderStrong)}
                      onClick={() => handleAccess('entrada', verifyResult.allowed ? 'permitido' : 'denegado', verifyResult.person?.rut)}
                    >
                      Registrar Entrada
                    </Button>
                    <Button
                      variant="outline"
                      className={classNames('', borderStrong)}
                      onClick={() => handleAccess('salida', verifyResult.allowed ? 'permitido' : 'denegado', verifyResult.person?.rut)}
                    >
                      Registrar Salida
                    </Button>
                  </div>
                </div>
              )}
            </div>

            <div className={classNames('pt-2 border-t', borderStrong)}>
              <h3 className="font-semibold mb-2">Accesos recientes</h3>
              <div className="max-h-72 overflow-auto divide-y">
                {recent.length === 0 && (
                  <div className="text-sm text-muted-foreground">Sin registros</div>
                )}
                {recent.map((item) => (
                  <div key={item.id} className="py-2 text-sm flex items-center justify-between gap-3">
                    <div>
                      <div className="font-medium">
                        {item.person ? item.person.name : '—'}
                      </div>
                      <div className="text-muted-foreground">
                        {item.person ? item.person.rut : 'Sin RUT'} · {new Date(item.occurred_at).toLocaleTimeString()}
                      </div>
                    </div>
                    <div className="shrink-0 flex items-center gap-2">
                      <span className={classNames('rounded-md px-2 py-0.5 border text-xs', borderStrong)}>{item.direction}</span>
                      <span className={classNames('rounded-md px-2 py-0.5 border text-xs', item.status === 'permitido' ? 'border-green-600' : 'border-red-600')}>{item.status}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </aside>
        </div>
      </div>
    </AppLayout>
  )
}
