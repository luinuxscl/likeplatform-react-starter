import React, { useEffect, useMemo, useState } from 'react'
import AppLayout from '@/layouts/app-layout'
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'

// Guardia UI (Inertia page name: "fcv/guard/index")
// Requisitos: búsqueda rápida por RUT/Nombre, una sola pantalla, alto contraste, fuente escalable

const formatTolerance = (course?: VerifyResponse['course']) => {
  if (!course) return null
  if (course.entry_tolerance_mode === 'none') return 'Acceso sin restricción de horario'

  const tol = course.entry_tolerance_minutes ?? Number(course.entry_tolerance_mode)
  const minutes = Number.isFinite(tol) ? tol : 20

  return `Tolerancia de entrada: ${minutes} minutos`
}

type Person = {
  id: number
  name: string
  rut: string
  status: string
  memberships: Array<{
    role: string
    organization: { id: number; name: string; acronym: string | null }
  }>
  courses: string[]
}

type VerifyResponse = {
  allowed: boolean
  status: 'permitido' | 'denegado'
  reason?: string
  person?: { id: number; rut: string; name: string }
  organization?: { id: number; name: string; acronym: string | null }
  course?: {
    id: number
    name: string
    entry_tolerance_mode: string
    entry_tolerance_minutes: number | null
  }
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
  const [personDialogOpen, setPersonDialogOpen] = useState(false)
  const [selectedPerson, setSelectedPerson] = useState<Person | null>(null)

  const resolveAccessStatus = (person: Person) => {
    if (!person.memberships || person.memberships.length === 0) return 'unknown'
    if (person.status && person.status !== 'active') return 'denied'
    return 'allowed'
  }

  const resolveType = (person: Person) => {
    const roles = person.memberships?.map((m) => m.role.toLowerCase()) ?? []
    if (roles.includes('alumno')) return 'A'
    if (roles.includes('funcionario')) return 'F'
    return '-'
  }

  const primaryOrganization = (person: Person): string => {
    if (!person.memberships || person.memberships.length === 0) return '—'
    const org = person.memberships[0]?.organization
    if (!org) return '—'
    return org.acronym ? `${org.acronym} · ${org.name}` : org.name
  }

  const firstCourse = (person: Person): string => {
    if (!person.courses || person.courses.length === 0) return '—'
    return person.courses[0] ?? '—'
  }

  const handleRowClick = (person: Person) => {
    setSelectedPerson(person)
    setPersonDialogOpen(true)
  }

  // Buscar por nombre o RUT
  useEffect(() => {
    let active = true
    async function run() {
      if (!debouncedQuery) {
        setResults([])
        setSearching(false)
        return
      }

      setSearching(true)
      try {
        const term = looksLikeRut(debouncedQuery) ? normalizeRut(debouncedQuery) : debouncedQuery
        const res = await fetch(`/fcv/search?q=${encodeURIComponent(term)}`, {
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
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
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
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead className={classNames('text-left uppercase text-xs text-muted-foreground border-b', borderStrong)}>
                  <tr>
                    <th className="px-3 py-2"></th>
                    <th className="px-3 py-2">RUT</th>
                    <th className="px-3 py-2">Tipo</th>
                    <th className="px-3 py-2">Nombre</th>
                    <th className="px-3 py-2">Organización</th>
                    <th className="px-3 py-2">Curso</th>
                    <th className="px-3 py-2 text-right"></th>
                  </tr>
                </thead>
                <tbody>
                  {results.length === 0 && (
                    <tr>
                      <td colSpan={7} className="px-3 py-6 text-center text-muted-foreground">
                        Sin resultados
                      </td>
                    </tr>
                  )}
                  {results.map((p) => {
                    const accessStatus = resolveAccessStatus(p)
                    const indicatorColor =
                      accessStatus === 'allowed'
                        ? 'bg-emerald-500'
                        : accessStatus === 'denied'
                          ? 'bg-red-500'
                          : 'bg-muted-foreground'

                    return (
                      <tr
                        key={p.id}
                        className="hover:bg-muted/40 cursor-pointer"
                        onClick={() => handleRowClick(p)}
                      >
                        <td className="px-3 py-3">
                          <span className={classNames('inline-flex h-3 w-3 rounded-full', indicatorColor)} aria-hidden />
                          <span className="sr-only">{accessStatus}</span>
                        </td>
                        <td className="px-3 py-3 font-mono text-xs uppercase">{p.rut}</td>
                        <td className="px-3 py-3 font-semibold">{resolveType(p)}</td>
                        <td className="px-3 py-3">
                          <div className="flex flex-col gap-1">
                            <span className="font-medium">{p.name}</span>
                            {p.status !== 'active' && (
                              <span className="text-xs text-muted-foreground">Estado: {p.status}</span>
                            )}
                            {p.memberships.length > 0 && (
                              <div className="flex flex-wrap gap-1">
                                {p.memberships.map((m, idx) => (
                                  <span
                                    key={`${m.organization.id}-${idx}`}
                                    className="rounded-md border border-border bg-background px-2 py-0.5 text-xs"
                                  >
                                    {m.role} · {m.organization.acronym
                                      ? `${m.organization.acronym} · ${m.organization.name}`
                                      : m.organization.name}
                                  </span>
                                ))}
                              </div>
                            )}
                          </div>
                        </td>
                        <td className="px-3 py-3 text-sm">{primaryOrganization(p)}</td>
                        <td className="px-3 py-3 text-sm">{resolveType(p) === 'A' ? firstCourse(p) : '—'}</td>
                        <td
                          className="px-3 py-3"
                          onClick={(e) => {
                            e.stopPropagation()
                          }}
                        >
                          <div className="flex items-center justify-end gap-2">
                            <Button size="sm" variant="outline" className={borderStrong} onClick={() => handleRowClick(p)}>
                              Ver
                            </Button>
                            <Button
                              size="sm"
                              variant="outline"
                              className={borderStrong}
                              onClick={() => handleAccess('entrada', 'permitido', p.rut)}
                            >
                              Entrada
                            </Button>
                            <Button
                              size="sm"
                              variant="outline"
                              className={borderStrong}
                              onClick={() => handleAccess('salida', 'permitido', p.rut)}
                            >
                              Salida
                            </Button>
                          </div>
                        </td>
                      </tr>
                    )
                  })}
                </tbody>
              </table>
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
                      <span className="text-xs text-muted-foreground">
                        {verifyResult.organization.acronym
                          ? `${verifyResult.organization.acronym} · ${verifyResult.organization.name}`
                          : verifyResult.organization.name}
                      </span>
                    )}
                    {verifyResult.course && (
                      <span className="text-xs text-muted-foreground">
                        Curso: {verifyResult.course.name}
                      </span>
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
                  {verifyResult.course && (
                    <div className="mt-2 text-sm space-y-1">
                      <div>
                        <span className="font-semibold">Curso:</span> {verifyResult.course.name}
                      </div>
                      {formatTolerance(verifyResult.course) && (
                        <div className="text-muted-foreground">{formatTolerance(verifyResult.course)}</div>
                      )}
                    </div>
                  )}
                  <div className="mt-3 flex items-center gap-2">
                    <Button
                      variant="outline"
                      className={classNames('', borderStrong)}
                      disabled={!verifyResult.allowed}
                      onClick={() =>
                        handleAccess('entrada', verifyResult.allowed ? 'permitido' : 'denegado', verifyResult.person?.rut)
                      }
                    >
                      Registrar Entrada
                    </Button>
                    <Button
                      variant="outline"
                      className={classNames('', borderStrong)}
                      disabled={!verifyResult.allowed}
                      onClick={() =>
                        handleAccess('salida', verifyResult.allowed ? 'permitido' : 'denegado', verifyResult.person?.rut)
                      }
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

        <Dialog open={personDialogOpen} onOpenChange={setPersonDialogOpen}>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Detalle de persona</DialogTitle>
            </DialogHeader>
            {selectedPerson ? (
              <div className="space-y-4 text-sm">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <div className="text-muted-foreground uppercase text-xs">Nombre</div>
                    <div className="font-medium">{selectedPerson.name}</div>
                  </div>
                  <div>
                    <div className="text-muted-foreground uppercase text-xs">RUT</div>
                    <div className="font-mono text-xs">{selectedPerson.rut}</div>
                  </div>
                  <div>
                    <div className="text-muted-foreground uppercase text-xs">Estado</div>
                    <div>{selectedPerson.status}</div>
                  </div>
                  <div>
                    <div className="text-muted-foreground uppercase text-xs">Tipo</div>
                    <div>{resolveType(selectedPerson)}</div>
                  </div>
                </div>

                <div>
                  <div className="text-muted-foreground uppercase text-xs mb-2">Membresías</div>
                  <div className="space-y-2">
                    {selectedPerson.memberships.length === 0 && <div className="text-muted-foreground">Sin membresías</div>}
                    {selectedPerson.memberships.map((m, idx) => (
                      <div key={`${m.organization.id}-${idx}`} className="flex flex-wrap items-center gap-2">
                        <Badge variant="outline" className="text-xs capitalize">{m.role}</Badge>
                        <Badge variant="secondary" className="text-xs">
                          {m.organization.acronym
                            ? `${m.organization.acronym} · ${m.organization.name}`
                            : m.organization.name}
                        </Badge>
                      </div>
                    ))}
                  </div>
                </div>

                <div>
                  <div className="text-muted-foreground uppercase text-xs mb-2">Cursos</div>
                  {selectedPerson.courses.length === 0 && <div className="text-muted-foreground">Sin cursos asignados</div>}
                  <ul className="list-disc list-inside space-y-1">
                    {selectedPerson.courses.map((course) => (
                      <li key={course}>{course}</li>
                    ))}
                  </ul>
                </div>

                <div className="flex justify-end gap-2">
                  <Button variant="outline" onClick={() => selectedPerson && handleVerify(selectedPerson.rut)}>
                    Verificar
                  </Button>
                  <Button variant="outline" onClick={() => selectedPerson && handleAccess('entrada', 'permitido', selectedPerson.rut)}>
                    Registrar Entrada
                  </Button>
                  <Button variant="outline" onClick={() => selectedPerson && handleAccess('salida', 'permitido', selectedPerson.rut)}>
                    Registrar Salida
                  </Button>
                </div>
              </div>
            ) : (
              <div className="text-muted-foreground">Sin datos.</div>
            )}
          </DialogContent>
        </Dialog>
      </div>
    </AppLayout>
  )
}
