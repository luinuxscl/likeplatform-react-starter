import { useEffect, useMemo, useState } from 'react'
import useTheme from '@/hooks/useTheme'

export default function ThemeSelector() {
  const { currentTheme, availableThemes, setTheme, applyTheme } = useTheme()
  const [value, setValue] = useState(currentTheme)

  useEffect(() => {
    setValue(currentTheme)
    // Ensure CSS variables applied on mount
    applyTheme(currentTheme)
  }, [currentTheme, applyTheme])

  const items = useMemo(
    () => Object.entries(availableThemes).map(([key, cfg]) => ({ key, name: cfg.name, colors: cfg.colors })),
    [availableThemes]
  )

  if (!items.length) return null

  return (
    <div className="space-y-4">
      <div>
        <label htmlFor="theme" className="block text-sm font-medium">Theme</label>
        <select
          id="theme"
          value={value}
          onChange={(e) => setValue(e.target.value)}
          className="mt-1 block w-full rounded-md border border-[--border] bg-[--background] px-3 py-2 text-sm"
        >
          {items.map((item) => (
            <option key={item.key} value={item.key}>{item.name}</option>
          ))}
        </select>
      </div>

      <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
        {items.map((item) => (
          <button
            key={item.key}
            type="button"
            onClick={() => { setValue(item.key); setTheme(item.key) }}
            className={`group flex flex-col items-start gap-2 rounded-lg border p-3 text-left transition hover:shadow ${value === item.key ? 'ring-2 ring-[--ring]' : ''}`}
            style={{ borderColor: 'var(--border)' }}
          >
            <div className="flex items-center gap-2">
              <span className="text-sm font-medium">{item.name}</span>
              {value === item.key && <span className="text-xs text-[--muted-foreground]">(selected)</span>}
            </div>
            <div className="flex gap-2">
              {Object.entries(item.colors).slice(0, 6).map(([k, v]) => (
                <span key={k} className="h-5 w-5 rounded border" style={{ backgroundColor: v, borderColor: 'var(--border)' }} title={k} />
              ))}
            </div>
          </button>
        ))}
      </div>

      <div>
        <button
          type="button"
          onClick={() => setTheme(value)}
          className="inline-flex items-center rounded-md bg-[--primary] px-3 py-2 text-sm font-medium text-[--primary-foreground] hover:opacity-90"
        >
          Apply theme
        </button>
      </div>
    </div>
  )
}
