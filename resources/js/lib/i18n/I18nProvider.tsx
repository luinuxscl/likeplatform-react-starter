import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { router, usePage } from '@inertiajs/react'

export type Translations = Record<string, string>

export type I18nContextValue = {
  locale: string
  messages: Translations
  t: (key: string) => string
  setLocale: (next: 'en' | 'es') => Promise<void>
}

const I18nContext = createContext<I18nContextValue | null>(null)

async function fetchMessages(locale: string): Promise<Translations> {
  const res = await fetch(`/i18n/${locale}.json`, { headers: { 'Accept': 'application/json' } })
  if (!res.ok) return {}
  return res.json().catch(() => ({}))
}

export function I18nProvider({ children }: { children: React.ReactNode }) {
  const page = usePage<{ i18n?: { locale: string; available: string[] } }>()
  const initialLocale = page.props.i18n?.locale ?? 'en'

  const [locale, setLocaleState] = useState<string>(initialLocale)
  const [messages, setMessages] = useState<Translations>({})

  // load messages on mount and when locale changes
  useEffect(() => {
    let alive = true
    fetchMessages(locale).then((m) => { if (alive) setMessages(m) })
    return () => { alive = false }
  }, [locale])

  const t = useCallback((key: string) => {
    return messages[key] ?? key
  }, [messages])

  const setLocale = useCallback(async (next: 'en' | 'es') => {
    // optimistic switch
    setLocaleState(next)
    await router.patch('/locale', { locale: next }, { preserveScroll: true, preserveState: true })
    const m = await fetchMessages(next)
    setMessages(m)
  }, [])

  const value = useMemo<I18nContextValue>(() => ({ locale, messages, t, setLocale }), [locale, messages, t, setLocale])

  return (
    <I18nContext.Provider value={value}>
      {children}
    </I18nContext.Provider>
  )
}

export function useI18n() {
  const ctx = useContext(I18nContext)
  if (!ctx) throw new Error('useI18n must be used within I18nProvider')
  return ctx
}
