import { useEffect, useMemo, useState } from 'react'
import { useTheme } from '@/hooks/useShadcnTheme'
import { usePage } from '@inertiajs/react'

export default function ThemeSelector() {
  const page = usePage()
  const { currentTheme, availableThemes, setTheme, applyTheme, defaultTheme, resetDefaults } = useTheme()
  const [value, setValue] = useState(currentTheme)

  useEffect(() => {
    setValue(currentTheme)
    // Ensure CSS variables applied on mount
    applyTheme(currentTheme)
  }, [currentTheme, applyTheme])

  useEffect(() => {
    // Debug: verify props arrive
    // eslint-disable-next-line no-console
    console.log('[ThemeSelector] page.props.expansion:', (page as any).props?.expansion)
    // eslint-disable-next-line no-console
    console.log('[ThemeSelector] availableThemes keys:', Object.keys(availableThemes))
  }, [page, availableThemes])

  const items = useMemo(
    () => Object.entries(availableThemes).map(([key, cfg]) => ({ key, name: cfg.name, colors: cfg.colors })),
    [availableThemes]
  )

  if (!items.length) {
    return (
      <div className="rounded-lg border p-4" style={{ borderColor: 'var(--border)' }}>
        <h3 className="mb-2 text-sm font-semibold">Theme selector</h3>
        <p className="text-sm text-[--muted-foreground]">No themes available. Ensure config is loaded and shared via Inertia.</p>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      <h3 className="text-sm font-semibold">Theme selector</h3>

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3" role="list" aria-label="Available themes">
        {items.map((item) => (
          <button
            key={item.key}
            type="button"
            role="listitem"
            aria-pressed={value === item.key}
            onClick={() => { setValue(item.key); setTheme(item.key) }}
            className={`group flex flex-col items-start gap-2 rounded-lg border p-3 text-left transition hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-[--ring] ${value === item.key ? 'ring-2 ring-[--ring]' : ''}`}
            style={{ borderColor: 'var(--border)' }}
          >
            <div className="flex items-center gap-2">
              <span className="text-sm font-medium">{item.name}</span>
              {value === item.key && <span className="text-xs text-[--muted-foreground]">(selected)</span>}
            </div>
            <div className="flex flex-wrap gap-1 rounded-md border p-2 overflow-hidden" style={{ borderColor: 'var(--border)' }}>
              {Object.entries(item.colors).slice(0, 10).map(([k, v]) => (
                <span
                  key={k}
                  className="h-4 w-4 shrink-0 rounded border"
                  style={{ backgroundColor: v, borderColor: 'var(--border)' }}
                  title={k}
                />
              ))}
            </div>
            <span className="sr-only">Apply {item.name} theme</span>
          </button>
        ))}
      </div>
      <div className="pt-2">
        <button
          type="button"
          onClick={() => { setValue(defaultTheme); resetDefaults() }}
          className="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-[--muted]"
          style={{ borderColor: 'var(--border)' }}
        >
          Reset to defaults ({defaultTheme})
        </button>
      </div>
    </div>
  )
}
