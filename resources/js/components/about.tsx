import { toast } from 'sonner'
import { router, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'

export function useAbout() {
  const { t } = useI18n()
  const page = usePage()
  const version = (page.props as any)?.version || '0.1.0-dev'
  const app = (page.props as any)?.app || {}
  const description = app?.description || ''

  const showAbout = () => {
    toast(
      (
        <div className="flex max-w-[480px] flex-col gap-2">
          <div className="text-sm font-medium">LikePlatform React Starter</div>
          <div className="text-xs text-muted-foreground">{t('Versión')} v{version}</div>
          <div className="text-xs text-muted-foreground">{description}</div>
          <div className="mt-1 flex flex-wrap items-center gap-2">
            <button
              onClick={() => router.visit('/changelog')}
              className="inline-flex items-center rounded-md border border-border bg-card px-2 py-1 text-xs hover:bg-muted"
            >
              {t('Ver Changelog')}
            </button>
            <a
              href="https://github.com/luinuxscl"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center rounded-md border border-border bg-card px-2 py-1 text-xs hover:bg-muted"
            >
              {t('GitHub del desarrollador')}
            </a>
            <span className="text-xs text-muted-foreground">{t('Autor')}: Luis Sepúlveda (luinuxSCL)</span>
          </div>
        </div>
      ),
      { duration: 10000 }
    )
  }

  return { showAbout, version, description }
}
