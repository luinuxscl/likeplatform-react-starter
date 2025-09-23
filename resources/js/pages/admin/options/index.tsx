import AppLayout from '@/layouts/app-layout'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, useForm } from '@inertiajs/react'
import { useI18n } from '@/lib/i18n/I18nProvider'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { useMemo } from 'react'

// Types received from backend
type Field = { key: string; label: string; type: string; group?: string }

export default function AdminOptionsIndex({ schema, values }: { schema: Field[]; values: Record<string, any> }) {
  const { t } = useI18n()
  const { data, setData, put, processing, errors } = useForm<{ values: Record<string, any> }>({
    values: values || {},
  })

  const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
    { title: t('Administración'), href: '/admin/users' },
    { title: t('Opciones'), href: '/admin/options' },
  ], [t])

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    put('/admin/options')
  }

  const grouped = useMemo(() => {
    const g: Record<string, Field[]> = {}
    for (const f of schema) {
      const key = f.group || 'general'
      g[key] = g[key] || []
      g[key].push(f)
    }
    return g
  }, [schema])

  const renderField = (f: Field) => {
    const v = data.values[f.key] ?? ''
    const onChange = (val: any) => setData('values', { ...data.values, [f.key]: val })

    if (f.type === 'text') {
      return (
        <div className="flex flex-col gap-2">
          <Label htmlFor={f.key}>{f.label}</Label>
          <Textarea id={f.key} value={v} onChange={(e) => onChange(e.target.value)} rows={4} />
          {errors[`values.${f.key}` as any] && <p className="text-sm text-red-500">{String((errors as any)[`values.${f.key}`])}</p>}
        </div>
      )
    }

    return (
      <div className="flex flex-col gap-2">
        <Label htmlFor={f.key}>{f.label}</Label>
        <Input id={f.key} type={f.type === 'integer' ? 'number' : f.type === 'url' ? 'url' : 'text'} value={v} onChange={(e) => onChange(e.target.value)} />
        {errors[`values.${f.key}` as any] && <p className="text-sm text-red-500">{String((errors as any)[`values.${f.key}`])}</p>}
      </div>
    )
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={t('Administración - Opciones')} />
      <form onSubmit={submit} className="flex flex-col gap-6 p-4">
        {Object.entries(grouped).map(([group, fields]) => (
          <div key={group} className="rounded-xl border border-border p-4">
            <div className="mb-3 flex items-center gap-2 text-sm font-medium uppercase">
              <span className="inline-block h-2 w-2 rounded-full bg-primary" />
              <span className="text-primary/90">{t(group)}</span>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              {fields.map((f) => (
                <div key={f.key}>{renderField(f)}</div>
              ))}
            </div>
          </div>
        ))}
        <div className="mt-2 flex items-center gap-2">
          <Button type="submit" variant="default" disabled={processing}>{t('Guardar')}</Button>
          <Link href="/admin/users" className="text-sm text-muted-foreground hover:underline">{t('Cancelar')}</Link>
        </div>
      </form>
    </AppLayout>
  )
}
