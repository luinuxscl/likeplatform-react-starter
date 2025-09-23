import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, usePage } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'

export default function ChangelogPage() {
  const { t } = useI18n()
  const page = usePage<{ props: { content: string } }>()
  const content = (page.props as any)?.content || ''

  const breadcrumbs: BreadcrumbItem[] = [
    { title: t('Plataforma'), href: '/dashboard' },
    { title: t('Changelog'), href: '/changelog' },
  ]

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={t('Changelog')} />
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="text-lg font-medium">{t('Changelog')}</div>
        <div className="rounded-xl border border-border bg-card p-4">
          {content ? (
            <pre className="whitespace-pre-wrap text-sm leading-6">{content}</pre>
          ) : (
            <div className="text-muted-foreground">{t('No hay entradas aún.')}</div>
          )}
        </div>
      </div>
    </AppLayout>
  )
}
