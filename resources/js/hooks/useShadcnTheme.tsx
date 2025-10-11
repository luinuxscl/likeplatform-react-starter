import { router, usePage } from '@inertiajs/react'
import { useCallback, useMemo } from 'react'

// Types come from resources/js/types/expansion.d.ts

type AvailableThemes = Record<string, ThemeConfig>

type UseThemeReturn = {
  currentTheme: string
  availableThemes: AvailableThemes
  setTheme: (theme: string) => void
  applyTheme: (theme: string) => void
  defaultTheme: string
  resetDefaults: () => void
}

export function useTheme(): UseThemeReturn {
  const page = usePage<{ expansion?: { themes?: { current: string; available: AvailableThemes; default: string } } }>()

  const current = page.props.expansion?.themes?.current ?? 'zinc'
  const available = page.props.expansion?.themes?.available ?? {}
  const defaultTheme = page.props.expansion?.themes?.default ?? 'zinc'

  const applyTheme = useCallback((theme: string) => {
    const cfg = available[theme]
    if (!cfg) return

    const root = document.documentElement
    const colors = cfg.colors || {}

    // Apply CSS variables selectively to avoid conflicting with the existing
    // light/dark system from use-appearance. Do NOT override background/foreground
    // and related surface tokens that are controlled by the dark class.
    const safeKeys = new Set([
      'primary',
      'primary-foreground',
      'accent',
      'accent-foreground',
      'ring',
    ])

    Object.entries(colors).forEach(([key, value]) => {
      if (safeKeys.has(key)) {
        root.style.setProperty(`--${key}`, value)
      }
    })
  }, [available])

  const setTheme = useCallback((theme: string) => {
    // Optimistically apply locally
    applyTheme(theme)

    // Persist on backend session
    router.patch('/expansion/themes', { theme }, { preserveScroll: true, preserveState: true })
  }, [applyTheme])

  const resetDefaults = useCallback(() => {
    try {
      localStorage.removeItem('appearance')
      // expire cookie
      document.cookie = 'appearance=; Max-Age=0; path=/'
    } catch {}

    applyTheme(defaultTheme)
    router.patch('/expansion/themes', { theme: defaultTheme }, { preserveScroll: true, preserveState: true })
  }, [applyTheme, defaultTheme])

  return useMemo(() => ({
    currentTheme: current,
    availableThemes: available,
    setTheme,
    applyTheme,
    defaultTheme,
    resetDefaults,
  }), [current, available, setTheme, applyTheme, defaultTheme, resetDefaults])
}

export default useTheme
