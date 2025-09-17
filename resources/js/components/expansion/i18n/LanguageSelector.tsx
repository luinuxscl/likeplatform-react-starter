import React from 'react'
import { useI18n } from '@/lib/i18n/I18nProvider'

type Props = { variant?: 'full' | 'compact' }

export default function LanguageSelector({ variant = 'full' }: Props) {
  const { locale, setLocale, t } = useI18n()

  const Button = ({ code, label }: { code: 'en' | 'es'; label: string }) => (
    <button
      type="button"
      aria-pressed={locale === code}
      onClick={() => setLocale(code)}
      className={`inline-flex items-center rounded-md border px-3 py-2 text-sm transition hover:bg-[--muted] ${
        locale === code ? 'ring-2 ring-[--ring]' : ''
      }`}
      style={{ borderColor: 'var(--border)' }}
    >
      {label}
    </button>
  )

  if (variant === 'compact') {
    // Mostrar solo el idioma activo; al hacer click alternar al otro
    const baseBtn = 'px-2.5 py-1 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[--ring]'
    const active = 'bg-[--primary] text-[--primary-foreground] shadow-sm'
    const currentLabel = locale === 'en' ? 'EN' : 'ES'
    const next = (locale === 'en' ? 'es' : 'en') as 'en' | 'es'
    const title = locale === 'en' ? 'Switch to Español' : 'Cambiar a English'
    return (
      <button
        type="button"
        aria-label={title}
        title={title}
        onClick={() => setLocale(next)}
        className={`${baseBtn} ${active} text-xs`}
      >
        {currentLabel}
      </button>
    )
  }

  return (
    <div className="space-y-2">
      <h3 className="text-sm font-semibold">{t('Language selector')}</h3>
      <div className="flex gap-2">
        <Button code="en" label={t('English')} />
        <Button code="es" label={t('Spanish')} />
      </div>
    </div>
  )
}
