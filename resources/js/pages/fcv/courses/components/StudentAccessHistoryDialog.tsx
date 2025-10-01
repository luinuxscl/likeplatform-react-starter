import React, { useEffect, useState } from 'react'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'

type AccessRecord = {
  id: number
  direction: string
  status: string
  created_at: string
}

type Props = {
  studentRut: string
  studentName: string
  open: boolean
  onOpenChange: (open: boolean) => void
}

export default function StudentAccessHistoryDialog({ studentRut, studentName, open, onOpenChange }: Props) {
  const [accesses, setAccesses] = useState<AccessRecord[]>([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (open && studentRut) {
      fetchAccessHistory()
    }
  }, [open, studentRut])

  const fetchAccessHistory = async () => {
    setLoading(true)
    try {
      // Simular fetch - en producción esto vendría de un endpoint real
      const response = await fetch(`/fcv/access/recent?rut=${encodeURIComponent(studentRut)}&limit=50`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      })

      if (response.ok) {
        const data = await response.json()
        setAccesses(data.items || [])
      }
    } catch (error) {
      console.error('Error fetching access history:', error)
    } finally {
      setLoading(false)
    }
  }

  const formatDate = (dateString: string) => {
    try {
      const date = new Date(dateString)
      return new Intl.DateTimeFormat('es-CL', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      }).format(date)
    } catch {
      return dateString
    }
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle>Historial de accesos</DialogTitle>
          <DialogDescription>
            {studentName} ({studentRut})
          </DialogDescription>
        </DialogHeader>

        <div className="max-h-96 space-y-2 overflow-y-auto">
          {loading ? (
            <div className="py-8 text-center text-sm text-muted-foreground">
              Cargando historial...
            </div>
          ) : accesses.length === 0 ? (
            <div className="py-8 text-center text-sm text-muted-foreground">
              No hay registros de acceso para este alumno.
            </div>
          ) : (
            accesses.map((access) => (
              <div
                key={access.id}
                className="flex items-center justify-between rounded-md border p-3"
              >
                <div className="flex items-center gap-3">
                  <div
                    className={`h-2 w-2 rounded-full ${
                      access.status === 'permitido' ? 'bg-green-500' : 'bg-red-500'
                    }`}
                  />
                  <div>
                    <div className="text-sm font-medium capitalize">{access.direction}</div>
                    <div className="text-xs text-muted-foreground">{formatDate(access.created_at)}</div>
                  </div>
                </div>
                <Badge
                  variant={access.status === 'permitido' ? 'default' : 'destructive'}
                  className="text-xs"
                >
                  {access.status}
                </Badge>
              </div>
            ))
          )}
        </div>

        {accesses.length > 0 && (
          <div className="border-t pt-3 text-xs text-muted-foreground">
            Mostrando últimos {accesses.length} accesos
          </div>
        )}
      </DialogContent>
    </Dialog>
  )
}
