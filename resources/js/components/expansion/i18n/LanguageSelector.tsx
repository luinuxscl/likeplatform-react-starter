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
    return (
      <div className="inline-flex items-center rounded-full border bg-[--background] p-0.5 text-xs" style={{ borderColor: 'var(--border)' }}>
        <button
          type="button"
          aria-pressed={locale === 'en'}
          onClick={() => setLocale('en')}
          className={`px-2 py-1 rounded-full transition ${locale === 'en' ? 'bg-[--muted] text-foreground' : 'text-[--muted-foreground]'}`}
        >
          EN
        </button>
        <button
          type="button"
          aria-pressed={locale === 'es'}
          onClick={() => setLocale('es')}
          className={`px-2 py-1 rounded-full transition ${locale === 'es' ? 'bg-[--muted] text-foreground' : 'text-[--muted-foreground]'}`}
        >
          ES
        </button>
      </div>
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
