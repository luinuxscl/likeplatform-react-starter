import React, { useEffect, useMemo, useState } from 'react'

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

function classNames(...xs: Array<string | false | undefined>) {
  return xs.filter(Boolean).join(' ')
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
  const [query, setQuery] = useState('')
  const debouncedQuery = useDebounced(query)
  const [searching, setSearching] = useState(false)
  const [results, setResults] = useState<Person[]>([])

  const [verifyLoading, setVerifyLoading] = useState(false)
  const [verifyResult, setVerifyResult] = useState<VerifyResponse | null>(null)

  const [fontIdx, setFontIdx] = useState(1) // default L
  const fontClass = useMemo(() => FONT_SIZES[fontIdx]?.value ?? 'text-base', [fontIdx])

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
        const res = await fetch('/fcv/search', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: JSON.stringify({ q: debouncedQuery }),
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

  async function handleVerify(rut: string) {
    setVerifyLoading(true)
    setVerifyResult(null)
    try {
      const res = await fetch('/fcv/verify', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ rut }),
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
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ rut: rut ?? null, direction, status }),
    })
    if (!res.ok) return
  }

  return (
    <div className={classNames('min-h-[calc(100vh-4rem)] bg-background text-foreground', fontClass)}>
      <div className="mx-auto max-w-7xl px-4 py-4 space-y-4">
        {/* Barra Superior: Búsqueda + Controles de tamaño */}
        <div className="flex flex-col gap-3 md:flex-row md:items-center">
          <div className="flex-1">
            <label className="block text-sm text-muted-foreground mb-1">Buscar por nombre o RUT</label>
            <input
              type="text"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Ej: 12345678k o Juan Pérez"
              className="w-full rounded-md border border-border bg-background px-3 py-2 outline-none ring-offset-background focus-visible:ring-2 focus-visible:ring-ring"
            />
          </div>
          <div className="flex items-center gap-2">
            <span className="text-sm text-muted-foreground">Tamaño</span>
            <div className="flex rounded-md border border-border overflow-hidden">
              {FONT_SIZES.map((fs, i) => (
                <button
                  key={fs.label}
                  className={classNames(
                    'px-3 py-2 hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                    i === fontIdx && 'bg-muted'
                  )}
                  onClick={() => setFontIdx(i)}
                >
                  {fs.label}
                </button>
              ))}
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
          {/* Columna 1: Resultados de búsqueda */}
          <section className="lg:col-span-2 rounded-lg border border-border bg-card">
            <div className="border-b border-border p-3">
              <h2 className="font-semibold">Resultados</h2>
              {searching && <p className="text-sm text-muted-foreground">Buscando…</p>}
            </div>
            <div className="divide-y divide-border">
              {results.length === 0 && (
                <div className="p-4 text-sm text-muted-foreground">Sin resultados</div>
              )}
              {results.map((p) => (
                <article key={p.id} className="p-3 flex items-start justify-between gap-3">
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <span className="font-medium">{p.name}</span>
                      <span className="rounded-md bg-muted px-2 py-0.5 text-xs text-muted-foreground border border-border">{p.rut}</span>
                    </div>
                    <div className="flex flex-wrap gap-2">
                      {p.memberships.map((m, idx) => (
                        <span
                          key={idx}
                          className="rounded-md border border-border bg-background px-2 py-0.5 text-xs"
                          title={`Regla: ${m.organization.access_rule_preset}`}
                        >
                          {m.role} · {m.organization.name}
                        </span>
                      ))}
                    </div>
                  </div>
                  <div className="flex shrink-0 items-center gap-2">
                    <button
                      className="rounded-md border border-border bg-primary/90 text-primary-foreground px-3 py-2 hover:bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                      onClick={() => handleVerify(p.rut)}
                    >
                      Verificar
                    </button>
                    <div className="flex items-center gap-1">
                      <button
                        className="rounded-md border border-border bg-background px-2 py-2 hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        onClick={() => handleAccess('entrada', 'permitido', p.rut)}
                        title="Registrar entrada"
                      >
                        ⬅︎
                      </button>
                      <button
                        className="rounded-md border border-border bg-background px-2 py-2 hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        onClick={() => handleAccess('salida', 'permitido', p.rut)}
                        title="Registrar salida"
                      >
                        ➝
                      </button>
                    </div>
                  </div>
                </article>
              ))}
            </div>
          </section>

          {/* Columna 2: Panel de verificación / resultado */}
          <aside className="rounded-lg border border-border bg-card p-3 space-y-3">
            <h2 className="font-semibold">Verificación</h2>
            <div className="space-y-2">
              <button
                className="w-full rounded-md border border-border bg-primary/90 text-primary-foreground px-3 py-2 hover:bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                onClick={() => {
                  if (query) handleVerify(query)
                }}
              >
                {verifyLoading ? 'Verificando…' : 'Verificar RUT/NOMBRE actual'}
              </button>
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
                    <button
                      className="rounded-md border border-border bg-background px-3 py-2 hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                      onClick={() => handleAccess('entrada', verifyResult.allowed ? 'permitido' : 'denegado', verifyResult.person?.rut)}
                    >
                      Registrar Entrada
                    </button>
                    <button
                      className="rounded-md border border-border bg-background px-3 py-2 hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                      onClick={() => handleAccess('salida', verifyResult.allowed ? 'permitido' : 'denegado', verifyResult.person?.rut)}
                    >
                      Registrar Salida
                    </button>
                  </div>
                </div>
              )}
            </div>

            <div className="pt-2 border-t border-border">
              <p className="text-sm text-muted-foreground">
                Consejo: usa el campo de búsqueda para filtrar por RUT o nombre. Ajusta el tamaño de fuente para mejorar la legibilidad en condiciones de luz.
              </p>
            </div>
          </aside>
        </div>
      </div>
    </div>
  )
}
