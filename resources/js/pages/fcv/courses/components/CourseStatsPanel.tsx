import React, { useEffect, useState } from 'react'
import { Badge } from '@/components/ui/badge'

type Stats = {
  students_total: number
  students_active: number
  attendance_rate_7d: number
  attendance_rate_30d: number
  access_count_by_day: Array<{ date: string; count: number }>
  top_students: Array<{ id: number; name: string; rut: string; access_count: number }>
  low_attendance_students: Array<{ id: number; name: string; rut: string; access_count: number }>
}

type Props = {
  courseId: number
}

export default function CourseStatsPanel({ courseId }: Props) {
  const [stats, setStats] = useState<Stats | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    fetchStats()
  }, [courseId])

  const fetchStats = async () => {
    setLoading(true)
    setError(null)

    try {
      const response = await fetch(`/fcv/courses/${courseId}/stats`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      })

      if (!response.ok) {
        throw new Error('Error al cargar estadísticas')
      }

      const data = await response.json()
      setStats(data)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Error desconocido')
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-sm text-muted-foreground">Cargando estadísticas...</div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="rounded-lg border border-destructive/50 bg-destructive/10 p-4">
        <p className="text-sm text-destructive">Error: {error}</p>
      </div>
    )
  }

  if (!stats) {
    return null
  }

  return (
    <div className="space-y-6">
      {/* KPIs principales */}
      <div className="grid gap-4 md:grid-cols-4">
        <div className="rounded-lg border p-4">
          <div className="text-sm text-muted-foreground">Total alumnos</div>
          <div className="mt-2 text-3xl font-bold">{stats.students_total}</div>
        </div>
        <div className="rounded-lg border p-4">
          <div className="text-sm text-muted-foreground">Alumnos activos</div>
          <div className="mt-2 text-3xl font-bold">{stats.students_active}</div>
        </div>
        <div className="rounded-lg border p-4">
          <div className="text-sm text-muted-foreground">Asistencia 7 días</div>
          <div className="mt-2 text-3xl font-bold">{stats.attendance_rate_7d}%</div>
        </div>
        <div className="rounded-lg border p-4">
          <div className="text-sm text-muted-foreground">Asistencia 30 días</div>
          <div className="mt-2 text-3xl font-bold">{stats.attendance_rate_30d}%</div>
        </div>
      </div>

      {/* Gráfico de accesos por día */}
      <div className="rounded-lg border p-6">
        <h3 className="mb-4 text-lg font-semibold">Accesos por día (últimos 30 días)</h3>
        {stats.access_count_by_day.length === 0 ? (
          <p className="text-sm text-muted-foreground">No hay datos de accesos en los últimos 30 días.</p>
        ) : (
          <div className="space-y-2">
            {stats.access_count_by_day.slice(-14).map((item) => {
              const maxCount = Math.max(...stats.access_count_by_day.map((d) => d.count))
              const percentage = maxCount > 0 ? (item.count / maxCount) * 100 : 0

              return (
                <div key={item.date} className="flex items-center gap-3">
                  <span className="w-24 text-xs text-muted-foreground">{item.date}</span>
                  <div className="flex-1">
                    <div className="h-6 rounded-md bg-muted">
                      <div
                        className="h-full rounded-md bg-primary"
                        style={{ width: `${percentage}%` }}
                      />
                    </div>
                  </div>
                  <span className="w-12 text-right text-sm font-medium">{item.count}</span>
                </div>
              )
            })}
          </div>
        )}
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        {/* Top estudiantes */}
        <div className="rounded-lg border p-6">
          <h3 className="mb-4 text-lg font-semibold">Mayor asistencia (30 días)</h3>
          {stats.top_students.length === 0 ? (
            <p className="text-sm text-muted-foreground">No hay datos disponibles.</p>
          ) : (
            <div className="space-y-3">
              {stats.top_students.map((student, index) => (
                <div key={student.id} className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <span className="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-xs font-medium">
                      {index + 1}
                    </span>
                    <div>
                      <div className="text-sm font-medium">{student.name}</div>
                      <div className="text-xs text-muted-foreground">{student.rut}</div>
                    </div>
                  </div>
                  <Badge variant="outline">{student.access_count} accesos</Badge>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Baja asistencia */}
        <div className="rounded-lg border p-6">
          <h3 className="mb-4 text-lg font-semibold">Alertas de baja asistencia (7 días)</h3>
          {stats.low_attendance_students.length === 0 ? (
            <p className="text-sm text-muted-foreground">No hay alertas. Todos los alumnos activos tienen buena asistencia.</p>
          ) : (
            <div className="space-y-3">
              {stats.low_attendance_students.map((student) => (
                <div key={student.id} className="flex items-center justify-between">
                  <div>
                    <div className="text-sm font-medium">{student.name}</div>
                    <div className="text-xs text-muted-foreground">{student.rut}</div>
                  </div>
                  <Badge variant="destructive">{student.access_count} accesos</Badge>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
