import { useEffect, useMemo, useRef, useState } from 'react'
import { useTheme } from '@/contexts/ThemeContext'

// Mini widget to switch theme in realtime with a smooth global transition.
// Minimal UI: small colored dots representing themes. Click to apply.

export default function ThemeSwitcherMini() {
  const { currentTheme, availableThemes, setTheme, applyTheme } = useTheme()
  const [value, setValue] = useState(currentTheme)
  const timeoutRef = useRef<number | null>(null)

  useEffect(() => {
    setValue(currentTheme)
  }, [currentTheme])

  const items = useMemo(
    () => Object.entries(availableThemes).map(([key, cfg]) => ({ key, name: cfg.name, colors: cfg.colors })),
    [availableThemes]
  )

  const handlePick = (key: string) => {
    if (value === key) return
    setValue(key)
    // WOW effect: enable smooth transition on document
    const root = document.documentElement
    root.classList.add('theme-transition')
    setTheme(key)
    applyTheme(key)
    if (timeoutRef.current) window.clearTimeout(timeoutRef.current)
    timeoutRef.current = window.setTimeout(() => {
      root.classList.remove('theme-transition')
    }, 700)
  }

  if (!items.length) return null

  return (
    <div className="inline-flex items-center gap-2 rounded-md border px-2 py-1 text-xs text-muted-foreground"
         style={{ borderColor: 'var(--border)' }}
         aria-label="Theme switcher">
      <span className="hidden sm:inline">Theme</span>
      <div className="flex items-center gap-1">
        {items.slice(0, 6).map((it) => {
          const sample = Object.values(it.colors)[0] as string | undefined
          const active = value === it.key
          return (
            <button
              key={it.key}
              type="button"
              title={it.name}
              onClick={() => handlePick(it.key)}
              className={`relative inline-flex h-6 w-6 items-center justify-center rounded-full border transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-[--ring] ${active ? 'ring-2 ring-[--ring]' : ''}`}
              style={{ borderColor: 'var(--border)' }}
            >
              <span className="h-4 w-4 rounded-full" style={{ backgroundColor: sample ?? 'var(--primary)' }} />
            </button>
          )
        })}
      </div>
    </div>
  )
}
