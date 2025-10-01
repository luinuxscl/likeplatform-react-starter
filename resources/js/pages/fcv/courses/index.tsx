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

const weekdayOptions = [
  { value: 0, label: 'Domingo' },
  { value: 1, label: 'Lunes' },
  { value: 2, label: 'Martes' },
  { value: 3, label: 'Miércoles' },
  { value: 4, label: 'Jueves' },
  { value: 5, label: 'Viernes' },
  { value: 6, label: 'Sábado' },
]

function weekdayLabel(value: number) {
  return weekdayOptions.find((option) => option.value === value)?.label ?? `Día ${value}`
}

type OrganizationOption = {
  id: number
  name: string
  acronym: string | null
}

type CourseRow = {
  id: number
  organization: { id: number | null; name: string | null; acronym: string | null }
  name: string
  code: string | null
  description: string | null
  valid_from: string | null
  valid_until: string | null
  entry_tolerance_mode: string
  entry_tolerance_minutes: number | null
  entry_tolerance_label: string
  schedules: Array<{ id: number | null; day_of_week: number; start_time: string; end_time: string }>
  students_count: number
  deleted_at: string | null
  is_active: boolean
  can_hard_delete: boolean
  can_restore: boolean
}

type ToleranceOption = {
  value: string
  label: string
}

type PageProps = {
  courses: CourseRow[]
  organizations: OrganizationOption[]
  now: string
  toleranceOptions: ToleranceOption[]
}

type FormState = {
  organization_id: string
  name: string
  code: string
  description: string
  valid_from: string
  valid_until: string
  entry_tolerance_mode: string
  entry_tolerance_minutes: number | null
  schedules: Array<{ day_of_week: number; start_time: string; end_time: string }>
}

const emptyForm: FormState = {
  organization_id: '',
  name: '',
  code: '',
  description: '',
  valid_from: '',
  valid_until: '',
  entry_tolerance_mode: '20',
  entry_tolerance_minutes: 20,
  schedules: [{ day_of_week: 1, start_time: '09:00', end_time: '12:00' }],
}

function formatDate(value: string | null) {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat('es-CL', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
    }).format(new Date(value))
  } catch (err) {
    console.error(err)
    return value
  }
}

export default function CoursesIndex({ courses, organizations, toleranceOptions }: PageProps) {
  const [isDialogOpen, setIsDialogOpen] = useState(false)
  const [editingCourse, setEditingCourse] = useState<CourseRow | null>(null)

  const form = useForm<FormState>({ ...emptyForm })
  const fieldErrors = form.errors as Record<string, string>

  const breadcrumbs: BreadcrumbItem[] = useMemo(
    () => [
      { title: 'FCV', href: '/fcv/guard' },
      { title: 'Cursos', href: '/fcv/courses' },
    ],
    []
  )

  function closeDialog() {
    setIsDialogOpen(false)
    setEditingCourse(null)
    form.reset()
    form.clearErrors()
  }

  function openCreateDialog() {
    setEditingCourse(null)
    form.setData({ ...emptyForm })
    setIsDialogOpen(true)
  }

  function openEditDialog(course: CourseRow) {
    setEditingCourse(course)
    form.setData({
      organization_id: course.organization?.id ? String(course.organization.id) : '',
      name: course.name ?? '',
      code: course.code ?? '',
      description: course.description ?? '',
      valid_from: course.valid_from ?? '',
      valid_until: course.valid_until ?? '',
      entry_tolerance_mode: course.entry_tolerance_mode ?? '20',
      entry_tolerance_minutes: course.entry_tolerance_minutes ?? null,
      schedules:
        course.schedules.length > 0
          ? course.schedules.map((schedule) => ({
              day_of_week: schedule.day_of_week,
              start_time: schedule.start_time ?? '09:00',
              end_time: schedule.end_time ?? '12:00',
            }))
          : [...emptyForm.schedules],
    })
    setIsDialogOpen(true)
  }

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()

    form.transform((data) => {
      const mode = data.entry_tolerance_mode
      const sanitizedSchedules = data.schedules.map((schedule) => ({
        ...schedule,
        start_time: schedule.start_time ? schedule.start_time.slice(0, 5) : '',
        end_time: schedule.end_time ? schedule.end_time.slice(0, 5) : '',
      }))

      if (mode === 'none') {
        return {
          ...data,
          entry_tolerance_mode: 'none',
          entry_tolerance_minutes: null,
          schedules: sanitizedSchedules,
        }
      }

      const raw = mode && mode !== '' ? Number(mode) : Number(data.entry_tolerance_minutes ?? 20)
      const numeric = Number.isNaN(raw) ? 20 : raw

      return {
        ...data,
        entry_tolerance_mode: String(numeric),
        entry_tolerance_minutes: numeric,
        schedules: sanitizedSchedules,
      }
    })

    const resetTransform = () => form.transform((data) => data)

    if (editingCourse) {
      form.put(`/fcv/courses/${editingCourse.id}`, {
        preserveScroll: true,
        onSuccess: closeDialog,
        onFinish: resetTransform,
      })

      return
    }

    form.post('/fcv/courses', {
      preserveScroll: true,
      onSuccess: closeDialog,
      onFinish: resetTransform,
    })
  }

  function handleDelete(course: CourseRow) {
    const message = course.can_hard_delete
      ? 'Esta acción eliminará el curso de forma permanente. ¿Confirmas?'
      : 'Este curso tiene estudiantes; se archivará (soft delete). ¿Confirmas?'
    if (!window.confirm(message)) {
      return
    }

    router.delete(`/fcv/courses/${course.id}`, {
      preserveScroll: true,
    })
  }

  function handleRestore(course: CourseRow) {
    router.put(`/fcv/courses/${course.id}/restore`, undefined, {
      preserveScroll: true,
    })
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Gestión de cursos" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div className="flex flex-1 flex-col gap-6">
        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-2xl font-semibold">Cursos FCV</h1>
            <p className="text-sm text-muted-foreground">
              Administra cursos, su vigencia y la relación con organizaciones.
            </p>
          </div>
          <Button onClick={openCreateDialog}>Nuevo curso</Button>
        </div>

        <div className="overflow-x-auto rounded-lg border">
          <table className="min-w-full divide-y">
            <thead className="bg-muted/30 text-left text-xs uppercase text-muted-foreground">
              <tr>
                <th className="px-4 py-3">Código</th>
                <th className="px-4 py-3">Curso</th>
                <th className="px-4 py-3 text-center">Organización</th>
                <th className="px-4 py-3">Vigencia</th>
                <th className="px-4 py-3">Alumnos</th>
                <th className="px-4 py-3">Estado</th>
                <th className="px-4 py-3 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              {courses.length === 0 && (
                <tr>
                  <td className="px-4 py-8 text-center text-sm text-muted-foreground" colSpan={8}>
                    No hay cursos registrados.
                  </td>
                </tr>
              )}

              {courses.map((course) => {
                const isTrashed = Boolean(course.deleted_at)
                const statusBadge = course.is_active
                  ? { label: 'Activo', variant: 'default' as const }
                  : isTrashed
                    ? { label: 'Archivado', variant: 'secondary' as const }
                    : { label: 'Fuera de vigencia', variant: 'destructive' as const }

                return (
                  <tr
                    key={course.id}
                    className={classNames(
                      'border-b text-sm transition hover:bg-muted/40',
                      isTrashed && 'text-muted-foreground'
                    )}
                  >
                    <td className="px-4 py-3 font-mono text-xs">
                      {course.code ?? ''}
                    </td>
                    <td className="px-4 py-3">
                      
                      <div className="font-medium">{course.name}</div>
                      {course.description && (
                        <div className="text-xs text-muted-foreground line-clamp-1">{course.description}</div>
                      )}
                    </td>
                    <td className="px-4 py-3 text-center">
                      {course.organization?.acronym ?? course.organization?.name ?? '—'}
                    </td>
                    <td className="px-4 py-3 text-sm">
                      <div className="flex flex-col gap-1">
                        <span>
                          {course.valid_from || course.valid_until
                            ? `${formatDate(course.valid_from)} – ${formatDate(course.valid_until)}`
                            : 'Sin vigencia definida'}
                        </span>
                        <span className="text-xs text-muted-foreground">
                          {course.schedules.length > 0
                            ? course.schedules
                                .map((schedule) => `${weekdayLabel(schedule.day_of_week)} ${schedule.start_time}–${schedule.end_time}`)
                                .join(' · ')
                            : 'Sin horario configurado'}
                        </span>
                      </div>
                    </td>
                    <td className="px-4 py-3 text-sm">
                      <Badge variant="outline">{course.students_count}</Badge>
                    </td>
                    <td className="px-4 py-3 text-sm">
                      <Badge variant={statusBadge.variant}>{statusBadge.label}</Badge>
                    </td>
                    <td className="px-4 py-3 text-right">
                      <div className="flex justify-end gap-2">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => openEditDialog(course)}
                        >
                          Editar
                        </Button>

                        {course.can_restore ? (
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleRestore(course)}
                          >
                            Restaurar
                          </Button>
                        ) : (
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleDelete(course)}
                          >
                            {course.can_hard_delete ? 'Eliminar' : 'Archivar'}
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
            <DialogTitle>{editingCourse ? 'Editar curso' : 'Nuevo curso'}</DialogTitle>
            <DialogDescription>
              Los alumnos quedarán vinculados según la vigencia del curso. Ajusta la organización y fechas
              según corresponda.
            </DialogDescription>
          </DialogHeader>

          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <label className="text-sm font-medium" htmlFor="organization_id">
                  Organización
                </label>
                <Select
                  value={form.data.organization_id}
                  onValueChange={(value) => form.setData('organization_id', value)}
                >
                  <SelectTrigger id="organization_id" aria-invalid={Boolean(form.errors.organization_id)}>
                    <SelectValue placeholder="Selecciona organización" />
                  </SelectTrigger>
                  <SelectContent>
                    {organizations.map((org) => (
                      <SelectItem key={org.id} value={String(org.id)}>
                        {org.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {form.errors.organization_id && (
                  <p className="text-xs text-destructive">{form.errors.organization_id}</p>
                )}
              </div>

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
                <label className="text-sm font-medium" htmlFor="code">
                  Código
                </label>
                <Input
                  id="code"
                  value={form.data.code}
                  onChange={(event) => form.setData('code', event.target.value)}
                  aria-invalid={Boolean(form.errors.code)}
                />
                {form.errors.code && <p className="text-xs text-destructive">{form.errors.code}</p>}
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium" htmlFor="valid_from">
                  Vigencia desde
                </label>
                <Input
                  id="valid_from"
                  type="date"
                  value={form.data.valid_from}
                  onChange={(event) => form.setData('valid_from', event.target.value)}
                  aria-invalid={Boolean(form.errors.valid_from)}
                />
                {form.errors.valid_from && (
                  <p className="text-xs text-destructive">{form.errors.valid_from}</p>
                )}
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium" htmlFor="valid_until">
                  Vigencia hasta
                </label>
                <Input
                  id="valid_until"
                  type="date"
                  value={form.data.valid_until}
                  onChange={(event) => form.setData('valid_until', event.target.value)}
                  aria-invalid={Boolean(form.errors.valid_until)}
                />
                {form.errors.valid_until && (
                  <p className="text-xs text-destructive">{form.errors.valid_until}</p>
                )}
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium" htmlFor="entry_tolerance_mode">
                  Tolerancia de entrada
                </label>
                <Select
                  value={form.data.entry_tolerance_mode}
                  onValueChange={(value) => form.setData('entry_tolerance_mode', value)}
                >
                  <SelectTrigger
                    id="entry_tolerance_mode"
                    aria-invalid={Boolean(form.errors.entry_tolerance_mode)}
                  >
                    <SelectValue placeholder="Selecciona tolerancia" />
                  </SelectTrigger>
                  <SelectContent>
                    {toleranceOptions.map((option) => (
                      <SelectItem key={option.value} value={option.value}>
                        {option.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {form.errors.entry_tolerance_mode && (
                  <p className="text-xs text-destructive">{form.errors.entry_tolerance_mode}</p>
                )}
              </div>
            </div>

            <div className="space-y-4">
              <div className="flex items-start justify-between gap-4">
                <div>
                  <h3 className="text-sm font-medium">Horarios semanales</h3>
                  <p className="text-xs text-muted-foreground">
                    Registra al menos un bloque horario. Día y rango definen cuándo puede ingresar el alumnado.
                  </p>
                </div>
                <Button
                  type="button"
                  size="sm"
                  variant="outline"
                  onClick={() =>
                    form.setData('schedules', [
                      ...form.data.schedules,
                      { day_of_week: 1, start_time: '09:00', end_time: '12:00' },
                    ])
                  }
                >
                  Añadir horario
                </Button>
              </div>

              {form.data.schedules.length === 0 && (
                <p className="text-xs text-destructive">Debes registrar al menos un horario.</p>
              )}

              <div className="space-y-3">
                {form.data.schedules.map((schedule, index) => (
                  <div key={`${schedule.day_of_week}-${index}`} className="rounded-lg border p-3 space-y-3">
                    <div className="flex items-start justify-between gap-3">
                      <div className="space-y-2">
                        <label className="text-sm font-medium">Día de la semana</label>
                        <Select
                          value={String(schedule.day_of_week)}
                          onValueChange={(value) => {
                            const next = [...form.data.schedules]
                            next[index] = { ...next[index], day_of_week: Number(value) }
                            form.setData('schedules', next)
                          }}
                        >
                          <SelectTrigger className="w-40" aria-invalid={Boolean(form.errors[`schedules.${index}.day_of_week`])}>
                            <SelectValue placeholder="Selecciona día" />
                          </SelectTrigger>
                          <SelectContent>
                            {weekdayOptions.map((option) => (
                              <SelectItem key={option.value} value={String(option.value)}>
                                {option.label}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                        {form.errors[`schedules.${index}.day_of_week`] && (
                          <p className="text-xs text-destructive">
                            {form.errors[`schedules.${index}.day_of_week`] as string}
                          </p>
                        )}
                      </div>

                      <div className="space-y-2">
                        <label className="text-sm font-medium" htmlFor={`start_time_${index}`}>
                          Inicio
                        </label>
                        <Input
                          id={`start_time_${index}`}
                          type="time"
                          value={schedule.start_time}
                          onChange={(event) => {
                            const next = [...form.data.schedules]
                            next[index] = { ...next[index], start_time: event.target.value }
                            form.setData('schedules', next)
                          }}
                          aria-invalid={Boolean(form.errors[`schedules.${index}.start_time`])}
                        />
                        {form.errors[`schedules.${index}.start_time`] && (
                          <p className="text-xs text-destructive">
                            {form.errors[`schedules.${index}.start_time`] as string}
                          </p>
                        )}
                      </div>

                      <div className="space-y-2">
                        <label className="text-sm font-medium" htmlFor={`end_time_${index}`}>
                          Término
                        </label>
                        <Input
                          id={`end_time_${index}`}
                          type="time"
                          value={schedule.end_time}
                          onChange={(event) => {
                            const next = [...form.data.schedules]
                            next[index] = { ...next[index], end_time: event.target.value }
                            form.setData('schedules', next)
                          }}
                          aria-invalid={Boolean(form.errors[`schedules.${index}.end_time`])}
                        />
                        {form.errors[`schedules.${index}.end_time`] && (
                          <p className="text-xs text-destructive">
                            {form.errors[`schedules.${index}.end_time`] as string}
                          </p>
                        )}
                      </div>

                      <Button
                        type="button"
                        size="icon"
                        variant="ghost"
                        className="self-start"
                        onClick={() => {
                          const next = form.data.schedules.filter((_, idx) => idx !== index)
                          form.setData('schedules', next)
                        }}
                        disabled={form.data.schedules.length === 1}
                        title={form.data.schedules.length === 1 ? 'Debe existir al menos un horario' : 'Eliminar horario'}
                      >
                        ✕
                      </Button>
                    </div>
                  </div>
                ))}
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
                {editingCourse ? 'Guardar cambios' : 'Crear curso'}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </AppLayout>
  )
}
