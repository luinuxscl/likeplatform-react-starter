import React, { useState } from 'react'
import AppLayout from '@/layouts/app-layout'
import type { BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import CourseStudentImportDialog from './components/CourseStudentImportDialog'
import CourseAddStudentDialog from './components/CourseAddStudentDialog'
import StudentAccessHistoryDialog from './components/StudentAccessHistoryDialog'
import CourseScheduleEditor from './components/CourseScheduleEditor'
import CourseStatsPanel from './components/CourseStatsPanel'

type Organization = {
  id: number | null
  name: string | null
  acronym: string | null
}

type Schedule = {
  id: number | null
  day_of_week: number
  start_time: string
  end_time: string
}

type Course = {
  id: number
  organization: Organization
  name: string
  code: string | null
  description: string | null
  valid_from: string | null
  valid_until: string | null
  entry_tolerance_mode: string
  entry_tolerance_minutes: number | null
  entry_tolerance_label: string
  schedules: Schedule[]
  students_count: number
  is_active: boolean
  deleted_at: string | null
}

type Membership = {
  id: number
  role: string
  organization: {
    id: number
    name: string
    acronym: string | null
  }
  start_date: string | null
  end_date: string | null
}

type Student = {
  id: number
  name: string
  rut: string
  status: string
  contact_info: Record<string, string> | null
  memberships: Membership[]
}

type PaginatedStudents = {
  data: Student[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

type PageProps = {
  course: Course
  students: PaginatedStudents
}

const weekdayLabels: Record<number, string> = {
  0: 'Domingo',
  1: 'Lunes',
  2: 'Martes',
  3: 'Miércoles',
  4: 'Jueves',
  5: 'Viernes',
  6: 'Sábado',
}

export default function CourseShow({ course, students }: PageProps) {
  const [activeTab, setActiveTab] = useState('info')
  const [importDialogOpen, setImportDialogOpen] = useState(false)
  const [addStudentDialogOpen, setAddStudentDialogOpen] = useState(false)
  const [historyDialogOpen, setHistoryDialogOpen] = useState(false)
  const [selectedStudent, setSelectedStudent] = useState<{ rut: string; name: string } | null>(null)
  const [studentSearch, setStudentSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState<string>('all')

  const filteredStudents = students.data.filter((student) => {
    const matchesSearch =
      studentSearch === '' ||
      student.name.toLowerCase().includes(studentSearch.toLowerCase()) ||
      student.rut.toLowerCase().includes(studentSearch.toLowerCase())
    
    const matchesStatus = statusFilter === 'all' || student.status === statusFilter

    return matchesSearch && matchesStatus
  })

  const handleExportStudents = () => {
    const csv = [
      'RUT,Nombre,Estado,Email,Teléfono',
      ...filteredStudents.map((student) => {
        const email = student.contact_info?.email || ''
        const phone = student.contact_info?.phone || ''
        return `${student.rut},"${student.name}",${student.status},"${email}","${phone}"`
      }),
    ].join('\n')

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `alumnos-${course.code || course.id}-${new Date().toISOString().split('T')[0]}.csv`
    link.click()
    URL.revokeObjectURL(url)
  }

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'FCV', href: '/fcv/guard' },
    { title: 'Cursos', href: '/fcv/courses' },
    { title: course.name, href: `/fcv/courses/${course.id}` },
  ]

  const statusBadge = course.is_active
    ? { label: 'Activo', variant: 'default' as const }
    : course.deleted_at
      ? { label: 'Archivado', variant: 'secondary' as const }
      : { label: 'Inactivo', variant: 'destructive' as const }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${course.name} - Cursos FCV`} />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        {/* Header */}
        <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div className="flex-1">
            <div className="flex items-center gap-3">
              <h1 className="text-2xl font-semibold">{course.name}</h1>
              <Badge variant={statusBadge.variant}>{statusBadge.label}</Badge>
            </div>
            <div className="mt-2 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
              {course.code && (
                <span className="font-mono text-xs uppercase">{course.code}</span>
              )}
              {course.organization?.name && (
                <span>
                  {course.organization.acronym
                    ? `${course.organization.acronym} · ${course.organization.name}`
                    : course.organization.name}
                </span>
              )}
              <span>{course.students_count} alumnos</span>
              <span>{course.schedules.length} horarios</span>
            </div>
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              onClick={() => router.visit('/fcv/courses')}
            >
              Volver al listado
            </Button>
          </div>
        </div>

        {/* Tabs */}
        <Tabs value={activeTab} onValueChange={setActiveTab} className="flex-1">
          <TabsList>
            <TabsTrigger value="info">Información</TabsTrigger>
            <TabsTrigger value="schedules">Horarios</TabsTrigger>
            <TabsTrigger value="students">
              Alumnos ({course.students_count})
            </TabsTrigger>
            <TabsTrigger value="stats">Estadísticas</TabsTrigger>
          </TabsList>

          <TabsContent value="info" className="space-y-4">
            <div className="rounded-lg border p-6">
              <h2 className="mb-4 text-lg font-semibold">Información general</h2>
              <div className="grid gap-4 md:grid-cols-2">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Nombre</label>
                  <p className="mt-1">{course.name}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Código</label>
                  <p className="mt-1 font-mono text-xs">{course.code || '—'}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Organización</label>
                  <p className="mt-1">{course.organization?.name || '—'}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Tolerancia de entrada</label>
                  <p className="mt-1">{course.entry_tolerance_label}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Vigencia desde</label>
                  <p className="mt-1">{course.valid_from || '—'}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Vigencia hasta</label>
                  <p className="mt-1">{course.valid_until || '—'}</p>
                </div>
                {course.description && (
                  <div className="md:col-span-2">
                    <label className="text-sm font-medium text-muted-foreground">Descripción</label>
                    <p className="mt-1">{course.description}</p>
                  </div>
                )}
              </div>
            </div>
          </TabsContent>

          <TabsContent value="schedules" className="space-y-4">
            <div className="rounded-lg border p-6">
              <CourseScheduleEditor
                courseId={course.id}
                initialSchedules={course.schedules}
              />
            </div>
          </TabsContent>

          <TabsContent value="students" className="space-y-4">
            <div className="rounded-lg border">
              <div className="border-b p-4 space-y-4">
                <div className="flex items-center justify-between">
                  <h2 className="text-lg font-semibold">Alumnos inscritos</h2>
                  <div className="flex gap-2">
                    <Button size="sm" variant="outline" onClick={handleExportStudents}>
                      Exportar a Excel
                    </Button>
                    <Button size="sm" variant="outline" onClick={() => setAddStudentDialogOpen(true)}>
                      Añadir alumno
                    </Button>
                    <Button size="sm" onClick={() => setImportDialogOpen(true)}>
                      Importar desde Excel
                    </Button>
                  </div>
                </div>
                
                {/* Filtros y búsqueda */}
                <div className="flex gap-3">
                  <Input
                    placeholder="Buscar por nombre o RUT..."
                    value={studentSearch}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setStudentSearch(e.target.value)}
                    className="max-w-sm"
                  />
                  <select
                    value={statusFilter}
                    onChange={(e: React.ChangeEvent<HTMLSelectElement>) => setStatusFilter(e.target.value)}
                    className="rounded-md border border-input bg-background px-3 py-2 text-sm"
                  >
                    <option value="all">Todos los estados</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                  </select>
                  {(studentSearch || statusFilter !== 'all') && (
                    <Button
                      size="sm"
                      variant="ghost"
                      onClick={() => {
                        setStudentSearch('')
                        setStatusFilter('all')
                      }}
                    >
                      Limpiar filtros
                    </Button>
                  )}
                </div>
              </div>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y">
                  <thead className="bg-muted/30">
                    <tr>
                      <th className="px-4 py-3 text-left text-xs font-medium uppercase text-muted-foreground">
                        RUT
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium uppercase text-muted-foreground">
                        Nombre
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium uppercase text-muted-foreground">
                        Estado
                      </th>
                      <th className="px-4 py-3 text-left text-xs font-medium uppercase text-muted-foreground">
                        Contacto
                      </th>
                      <th className="px-4 py-3 text-right text-xs font-medium uppercase text-muted-foreground">
                        Acciones
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y">
                    {filteredStudents.length === 0 ? (
                      <tr>
                        <td colSpan={5} className="px-4 py-8 text-center text-sm text-muted-foreground">
                          {studentSearch || statusFilter !== 'all'
                            ? 'No se encontraron alumnos con los filtros aplicados.'
                            : 'No hay alumnos inscritos en este curso.'}
                        </td>
                      </tr>
                    ) : (
                      filteredStudents.map((student) => (
                        <tr key={student.id} className="hover:bg-muted/40">
                          <td className="px-4 py-3 font-mono text-xs">{student.rut}</td>
                          <td className="px-4 py-3">
                            <div className="font-medium">{student.name}</div>
                          </td>
                          <td className="px-4 py-3">
                            <Badge variant={student.status === 'active' ? 'default' : 'secondary'}>
                              {student.status}
                            </Badge>
                          </td>
                          <td className="px-4 py-3 text-sm text-muted-foreground">
                            {student.contact_info?.email || '—'}
                          </td>
                          <td className="px-4 py-3 text-right">
                            <div className="flex justify-end gap-2">
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => {
                                  setSelectedStudent({ rut: student.rut, name: student.name })
                                  setHistoryDialogOpen(true)
                                }}
                              >
                                Ver accesos
                              </Button>
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => {
                                  if (confirm('¿Desvincular este alumno del curso?')) {
                                    router.delete(`/fcv/courses/${course.id}/students/${student.id}`)
                                  }
                                }}
                              >
                                Desvincular
                              </Button>
                            </div>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
              {students.last_page > 1 && (
                <div className="border-t p-4">
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-muted-foreground">
                      Página {students.current_page} de {students.last_page} ({students.total} total)
                    </span>
                    <div className="flex gap-2">
                      <Button
                        size="sm"
                        variant="outline"
                        disabled={students.current_page === 1}
                        onClick={() => router.visit(`/fcv/courses/${course.id}?page=${students.current_page - 1}`)}
                      >
                        Anterior
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        disabled={students.current_page === students.last_page}
                        onClick={() => router.visit(`/fcv/courses/${course.id}?page=${students.current_page + 1}`)}
                      >
                        Siguiente
                      </Button>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </TabsContent>

          <TabsContent value="stats" className="space-y-4">
            <CourseStatsPanel courseId={course.id} />
          </TabsContent>
        </Tabs>

        {/* Modales */}
        <CourseStudentImportDialog
          courseId={course.id}
          open={importDialogOpen}
          onOpenChange={setImportDialogOpen}
        />
        <CourseAddStudentDialog
          courseId={course.id}
          open={addStudentDialogOpen}
          onOpenChange={setAddStudentDialogOpen}
        />
        {selectedStudent && (
          <StudentAccessHistoryDialog
            studentRut={selectedStudent.rut}
            studentName={selectedStudent.name}
            open={historyDialogOpen}
            onOpenChange={setHistoryDialogOpen}
          />
        )}
      </div>
    </AppLayout>
  )
}
