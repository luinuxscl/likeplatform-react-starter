import React, { FormEvent, useMemo, useState } from 'react'
import AppLayout from '@/layouts/app-layout'
import type { BreadcrumbItem } from '@/types'
import { Head, router, useForm } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

function classNames(...values: Array<string | false | null | undefined>) {
  return values.filter(Boolean).join(' ')
}

type OrganizationRow = {
  id: number
  name: string
  acronym: string | null
  type: string
  description: string | null
  courses_count: number
  memberships_count: number
  persons_count: number
  deleted_at: string | null
  can_hard_delete: boolean
  can_restore: boolean
}

type Option = {
  value: string
  label: string
}

type PageProps = {
  organizations: OrganizationRow[]
  types: Option[]
}

type FormState = {
  name: string
  acronym: string
  type: string
  description: string
}

const emptyForm: FormState = {
  name: '',
  acronym: '',
  type: '',
  description: '',
}

export default function OrganizationsIndex({ organizations, types }: PageProps) {
  const [isDialogOpen, setIsDialogOpen] = useState(false)
  const [editingOrganization, setEditingOrganization] = useState<OrganizationRow | null>(null)

  const form = useForm<FormState>({ ...emptyForm })

  const breadcrumbs: BreadcrumbItem[] = useMemo(
    () => [
      { title: 'FCV', href: '/fcv/guard' },
      { title: 'Organizaciones', href: '/fcv/organizations' },
    ],
    []
  )

  const typeLabels = useMemo(() => {
    return types.reduce<Record<string, string>>((acc, option) => {
      acc[option.value] = option.label
      return acc
    }, {})
  }, [types])

  function closeDialog() {
    setIsDialogOpen(false)
    setEditingOrganization(null)
    form.reset()
    form.clearErrors()
  }

  function openCreateDialog() {
    setEditingOrganization(null)
    form.setData({ ...emptyForm })
    setIsDialogOpen(true)
  }

  function openEditDialog(row: OrganizationRow) {
    setEditingOrganization(row)
    form.setData({
      name: row.name ?? '',
      acronym: row.acronym ?? '',
      type: row.type ?? '',
      description: row.description ?? '',
    })
    setIsDialogOpen(true)
  }

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (editingOrganization) {
      form.put(`/fcv/organizations/${editingOrganization.id}`, {
        preserveScroll: true,
        onSuccess: closeDialog,
      })
      return
    }

    form.post('/fcv/organizations', {
      preserveScroll: true,
      onSuccess: closeDialog,
    })
  }

  function handleDelete(row: OrganizationRow) {
    const message = row.can_hard_delete
      ? 'Esta acción eliminará la organización de forma permanente. ¿Deseas continuar?'
      : 'La organización tiene cursos o membresías, se archivará. ¿Deseas continuar?'

    if (!window.confirm(message)) {
      return
    }

    router.delete(`/fcv/organizations/${row.id}`, {
      preserveScroll: true,
    })
  }

  function handleRestore(row: OrganizationRow) {
    router.put(`/fcv/organizations/${row.id}/restore`, undefined, {
      preserveScroll: true,
    })
  }
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Organizaciones FCV" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="flex flex-1 flex-col gap-6">
          <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h1 className="text-2xl font-semibold">Organizaciones FCV</h1>
              <p className="text-sm text-muted-foreground">
                Gestiona organizaciones, sus reglas de acceso y relaciones con personas y cursos.
              </p>
            </div>
            <Button onClick={openCreateDialog}>Nueva organización</Button>
          </div>

          <div className="overflow-x-auto rounded-lg border">
            <table className="min-w-full divide-y">
              <thead className="bg-muted/30 text-left text-xs uppercase text-muted-foreground">
                <tr>
                  <th className="px-4 py-3">Organización</th>
                  <th className="px-4 py-3">Sigla</th>
                  <th className="px-4 py-3">Tipo</th>
                  <th className="px-4 py-3">Cursos</th>
                  <th className="px-4 py-3">Personas</th>
                  <th className="px-4 py-3">Estado</th>
                  <th className="px-4 py-3 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {organizations.length === 0 && (
                  <tr>
                    <td className="px-4 py-8 text-center text-sm text-muted-foreground" colSpan={7}>
                      No hay organizaciones registradas.
                    </td>
                  </tr>
                )}

                {organizations.map((org) => {
                  const isTrashed = Boolean(org.deleted_at)
                  const statusBadge = isTrashed
                    ? { label: 'Archivada', variant: 'secondary' as const }
                    : { label: 'Activa', variant: 'default' as const }

                  return (
                    <tr
                      key={org.id}
                      className={classNames(
                        'border-b text-sm transition hover:bg-muted/40',
                        isTrashed && 'text-muted-foreground'
                      )}
                    >
                      <td className="px-4 py-3">
                        <div className="font-medium">{org.name}</div>
                        {org.description && (
                          <div className="text-xs text-muted-foreground line-clamp-1">{org.description}</div>
                        )}
                      </td>
                      <td className="px-4 py-3 text-sm">{org.acronym ?? '—'}</td>
                      <td className="px-4 py-3 text-sm">{typeLabels[org.type] ?? org.type}</td>
                      <td className="px-4 py-3 text-sm">
                        <Badge variant="outline">{org.courses_count}</Badge>
                      </td>
                      <td className="px-4 py-3 text-sm">
                        <Badge variant="outline">{org.persons_count}</Badge>
                      </td>
                      <td className="px-4 py-3 text-sm">
                        <Badge variant={statusBadge.variant}>{statusBadge.label}</Badge>
                      </td>
                      <td className="px-4 py-3 text-right">
                        <div className="flex justify-end gap-2">
                          <Button variant="outline" size="sm" onClick={() => openEditDialog(org)}>
                            Editar
                          </Button>

                          {org.can_restore ? (
                            <Button variant="outline" size="sm" onClick={() => handleRestore(org)}>
                              Restaurar
                            </Button>
                          ) : (
                            <Button variant="outline" size="sm" onClick={() => handleDelete(org)}>
                              {org.can_hard_delete ? 'Eliminar' : 'Archivar'}
                            </Button>
                          )}
                        </div>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <Dialog open={isDialogOpen} onOpenChange={(open) => (!open ? closeDialog() : setIsDialogOpen(true))}>
        <DialogContent className="sm:max-w-2xl">
          <DialogHeader>
            <DialogTitle>{editingOrganization ? 'Editar organización' : 'Nueva organización'}</DialogTitle>
            <DialogDescription>
              Define la organización, su sigla (opcional) y el tipo para relacionarla con cursos y personas.
            </DialogDescription>
          </DialogHeader>

          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <label className="text-sm font-medium" htmlFor="name">
                  Nombre
                </label>
                <Input
                  id="name"
                  value={form.data.name}
                  onChange={(event) => form.setData('name', event.target.value)}
                  aria-invalid={Boolean(form.errors.name)}
                  required
                />
                {form.errors.name && <p className="text-xs text-destructive">{form.errors.name}</p>}
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium" htmlFor="acronym">
                  Sigla (opcional)
                </label>
                <Input
                  id="acronym"
                  value={form.data.acronym}
                  onChange={(event) => form.setData('acronym', event.target.value.toUpperCase())}
                  maxLength={20}
                  aria-invalid={Boolean(form.errors.acronym)}
                  placeholder="Ej: FCV"
                />
                {form.errors.acronym && <p className="text-xs text-destructive">{form.errors.acronym}</p>}
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium" htmlFor="type">
                  Tipo
                </label>
                <Select value={form.data.type} onValueChange={(value) => form.setData('type', value)}>
                  <SelectTrigger id="type" aria-invalid={Boolean(form.errors.type)}>
                    <SelectValue placeholder="Selecciona tipo" />
                  </SelectTrigger>
                  <SelectContent>
                    {types.map((option) => (
                      <SelectItem key={option.value} value={option.value}>
                        {option.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {form.errors.type && <p className="text-xs text-destructive">{form.errors.type}</p>}
              </div>

            </div>

            <div className="space-y-2">
              <label className="text-sm font-medium" htmlFor="description">
                Descripción
              </label>
              <Textarea
                id="description"
                value={form.data.description}
                onChange={(event) => form.setData('description', event.target.value)}
                rows={4}
              />
              {form.errors.description && (
                <p className="text-xs text-destructive">{form.errors.description}</p>
              )}
            </div>

            <DialogFooter className="flex flex-col gap-2 sm:flex-row sm:justify-end">
              <Button type="button" variant="outline" onClick={closeDialog}>
                Cancelar
              </Button>
              <Button type="submit" disabled={form.processing}>
                {editingOrganization ? 'Guardar cambios' : 'Crear organización'}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </AppLayout>
  )
}
