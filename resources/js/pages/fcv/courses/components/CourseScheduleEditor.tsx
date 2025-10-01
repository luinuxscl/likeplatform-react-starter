import React, { useState } from 'react'
import { router } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

type Schedule = {
  id: number | null
  day_of_week: number
  start_time: string
  end_time: string
}

type Props = {
  courseId: number
  initialSchedules: Schedule[]
  onUpdate?: () => void
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

export default function CourseScheduleEditor({ courseId, initialSchedules, onUpdate }: Props) {
  const [schedules, setSchedules] = useState<Schedule[]>(
    initialSchedules.length > 0
      ? initialSchedules
      : [{ id: null, day_of_week: 1, start_time: '09:00', end_time: '12:00' }]
  )
  const [editing, setEditing] = useState(false)
  const [saving, setSaving] = useState(false)

  const handleAddSchedule = () => {
    setSchedules([
      ...schedules,
      { id: null, day_of_week: 1, start_time: '09:00', end_time: '12:00' },
    ])
  }

  const handleRemoveSchedule = (index: number) => {
    if (schedules.length === 1) {
      alert('Debe existir al menos un horario')
      return
    }
    setSchedules(schedules.filter((_, i) => i !== index))
  }

  const handleUpdateSchedule = (index: number, field: keyof Schedule, value: string | number) => {
    const updated = [...schedules]
    updated[index] = { ...updated[index], [field]: value }
    setSchedules(updated)
  }

  const handleSave = async () => {
    setSaving(true)
    
    // Validar horarios
    for (let i = 0; i < schedules.length; i++) {
      const schedule = schedules[i]
      if (!schedule.start_time || !schedule.end_time) {
        alert(`Horario ${i + 1}: Debe completar inicio y término`)
        setSaving(false)
        return
      }
      if (schedule.start_time >= schedule.end_time) {
        alert(`Horario ${i + 1}: La hora de término debe ser posterior a la de inicio`)
        setSaving(false)
        return
      }
    }

    try {
      // Actualizar horarios del curso
      const response = await fetch(`/fcv/courses/${courseId}/schedules`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          schedules: schedules.map((s) => ({
            day_of_week: s.day_of_week,
            start_time: s.start_time,
            end_time: s.end_time,
          })),
        }),
        credentials: 'same-origin',
      })

      if (response.ok) {
        setEditing(false)
        router.reload()
        if (onUpdate) onUpdate()
      } else {
        alert('Error al guardar los horarios')
      }
    } catch (error) {
      console.error('Error saving schedules:', error)
      alert('Error al guardar los horarios')
    } finally {
      setSaving(false)
    }
  }

  const handleCancel = () => {
    setSchedules(
      initialSchedules.length > 0
        ? initialSchedules
        : [{ id: null, day_of_week: 1, start_time: '09:00', end_time: '12:00' }]
    )
    setEditing(false)
  }

  // Agrupar horarios por día
  const schedulesByDay = schedules.reduce((acc, schedule) => {
    if (!acc[schedule.day_of_week]) {
      acc[schedule.day_of_week] = []
    }
    acc[schedule.day_of_week].push(schedule)
    return acc
  }, {} as Record<number, Schedule[]>)

  if (!editing) {
    return (
      <div className="space-y-4">
        <div className="flex items-center justify-between">
          <h3 className="text-sm font-semibold">Horarios semanales</h3>
          <Button size="sm" variant="outline" onClick={() => setEditing(true)}>
            Editar horarios
          </Button>
        </div>

        {/* Vista de calendario semanal */}
        <div className="grid gap-2 md:grid-cols-7">
          {weekdayOptions.map((day) => (
            <div key={day.value} className="rounded-lg border p-3">
              <div className="mb-2 text-xs font-medium text-muted-foreground">{day.label}</div>
              <div className="space-y-1">
                {schedulesByDay[day.value]?.map((schedule, idx) => (
                  <Badge key={idx} variant="secondary" className="block text-center text-xs">
                    {schedule.start_time}–{schedule.end_time}
                  </Badge>
                )) || (
                  <span className="text-xs text-muted-foreground">—</span>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h3 className="text-sm font-semibold">Editar horarios</h3>
        <div className="flex gap-2">
          <Button size="sm" variant="outline" onClick={handleCancel} disabled={saving}>
            Cancelar
          </Button>
          <Button size="sm" onClick={handleSave} disabled={saving}>
            {saving ? 'Guardando...' : 'Guardar cambios'}
          </Button>
        </div>
      </div>

      <div className="space-y-3">
        {schedules.map((schedule, index) => (
          <div key={index} className="flex items-start gap-3 rounded-lg border p-3">
            <div className="flex-1 grid gap-3 md:grid-cols-3">
              <div className="space-y-2">
                <label className="text-xs font-medium">Día</label>
                <Select
                  value={String(schedule.day_of_week)}
                  onValueChange={(value) => handleUpdateSchedule(index, 'day_of_week', Number(value))}
                >
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    {weekdayOptions.map((option) => (
                      <SelectItem key={option.value} value={String(option.value)}>
                        {option.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <label className="text-xs font-medium">Inicio</label>
                <Input
                  type="time"
                  value={schedule.start_time}
                  onChange={(e) => handleUpdateSchedule(index, 'start_time', e.target.value)}
                />
              </div>

              <div className="space-y-2">
                <label className="text-xs font-medium">Término</label>
                <Input
                  type="time"
                  value={schedule.end_time}
                  onChange={(e) => handleUpdateSchedule(index, 'end_time', e.target.value)}
                />
              </div>
            </div>

            <Button
              size="icon"
              variant="ghost"
              onClick={() => handleRemoveSchedule(index)}
              disabled={schedules.length === 1}
              title={schedules.length === 1 ? 'Debe existir al menos un horario' : 'Eliminar'}
            >
              ✕
            </Button>
          </div>
        ))}
      </div>

      <Button size="sm" variant="outline" onClick={handleAddSchedule}>
        + Añadir horario
      </Button>
    </div>
  )
}
