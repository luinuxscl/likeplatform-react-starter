import * as Dialog from '@radix-ui/react-dialog'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { usePage, router } from '@inertiajs/react'
import { X } from 'lucide-react'
import { useMemo } from 'react'

export function AboutDialog({ open, onOpenChange }: { open: boolean; onOpenChange: (v: boolean) => void }) {
  const { t } = useI18n()
  const page = usePage()
  const version = (page.props as any)?.version || '0.1.0-dev'
  const app = (page.props as any)?.app || {}
  const description = app?.description || ''

  const projectName = useMemo(() => app?.name ?? 'LikePlatform React Starter', [app?.name])

  return (
    <Dialog.Root open={open} onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-50 bg-black/40 backdrop-blur-[2px] data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=open]:fade-in-0 data-[state=closed]:fade-out-0" />
        <Dialog.Content className="fixed left-1/2 top-1/2 z-50 w-[92vw] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl border border-border bg-card p-4 shadow-lg outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=open]:zoom-in-95 data-[state=closed]:zoom-out-95">
          <div className="mb-2 flex items-start justify-between gap-4">
            <Dialog.Title className="text-base font-semibold text-foreground">{t('Acerca de')}</Dialog.Title>
            <Dialog.Close className="inline-flex size-7 items-center justify-center rounded-md hover:bg-muted" aria-label={t('Cerrar')}>
              <X className="size-4" />
            </Dialog.Close>
          </div>
          <div className="space-y-2">
            <div className="text-sm font-medium text-foreground">{projectName}</div>
            <div className="text-xs text-muted-foreground">{t('Versión')} v{version}</div>
            <p className="text-sm text-muted-foreground">{description}</p>
          </div>
          <div className="mt-4 flex flex-wrap items-center gap-2">
            <button
              onClick={() => router.visit('/changelog')}
              className="inline-flex items-center rounded-md border border-border bg-card px-3 py-1.5 text-sm hover:bg-muted"
            >
              {t('Ver Changelog')}
            </button>
            <a
              href="https://github.com/luinuxscl/likeplatform-react-starter"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center rounded-md border border-border bg-card px-3 py-1.5 text-sm hover:bg-muted"
            >
              {t('Repositorio')}
            </a>
            <a
              href="https://github.com/luinuxscl"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center rounded-md border border-border bg-card px-3 py-1.5 text-sm hover:bg-muted"
            >
              {t('GitHub del desarrollador')}
            </a>
            <button
              onClick={() => {
                const text = `v${version}`
                if (navigator.clipboard?.writeText) navigator.clipboard.writeText(text)
              }}
              className="inline-flex items-center rounded-md border border-border bg-card px-3 py-1.5 text-sm hover:bg-muted"
            >
              {t('Copiar versión')}
            </button>
            <span className="ms-auto text-xs text-muted-foreground">{t('Autor')}: Luis Sepúlveda (luinuxSCL)</span>
          </div>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  )
}
