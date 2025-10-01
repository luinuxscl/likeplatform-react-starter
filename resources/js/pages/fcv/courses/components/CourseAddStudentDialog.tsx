import React, { useState } from 'react'
import { router } from '@inertiajs/react'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'

type SearchResult = {
  id: number
  name: string
  rut: string
  status: string
  memberships: Array<{
    role: string
    organization: { id: number; name: string }
  }>
  courses: string[]
}

type Props = {
  courseId: number
  open: boolean
  onOpenChange: (open: boolean) => void
}

export default function CourseAddStudentDialog({ courseId, open, onOpenChange }: Props) {
  const [searchQuery, setSearchQuery] = useState('')
  const [searching, setSearching] = useState(false)
  const [results, setResults] = useState<SearchResult[]>([])
  const [creating, setCreating] = useState(false)
  const [newStudent, setNewStudent] = useState({ rut: '', name: '', email: '', phone: '' })

  const handleSearch = async () => {
    if (!searchQuery.trim()) return

    setSearching(true)
    try {
      const response = await fetch(`/fcv/search?q=${encodeURIComponent(searchQuery)}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      })

      if (response.ok) {
        const data = await response.json()
        setResults(data.results || [])
      }
    } catch (error) {
      console.error('Error searching:', error)
    } finally {
      setSearching(false)
    }
  }

  const handleAttach = (personId: number) => {
    router.post(
      `/fcv/courses/${courseId}/students/${personId}`,
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          onOpenChange(false)
          setSearchQuery('')
          setResults([])
        },
      }
    )
  }

  const handleCreate = async () => {
    if (!newStudent.rut || !newStudent.name) {
      alert('RUT y Nombre son obligatorios')
      return
    }

    setCreating(true)
    try {
      // Crear persona mediante importación con un solo registro
      const formData = new FormData()
      const csvContent = `RUT,Nombre,Email,Telefono\n${newStudent.rut},${newStudent.name},${newStudent.email},${newStudent.phone}`
      const blob = new Blob([csvContent], { type: 'text/csv' })
      formData.append('file', blob, 'nuevo-alumno.csv')

      const response = await fetch(`/fcv/courses/${courseId}/students/import`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: formData,
        credentials: 'same-origin',
      })

      if (response.ok) {
        router.reload()
        onOpenChange(false)
        setNewStudent({ rut: '', name: '', email: '', phone: '' })
      } else {
        alert('Error al crear el alumno')
      }
    } catch (error) {
      console.error('Error creating:', error)
      alert('Error al crear el alumno')
    } finally {
      setCreating(false)
    }
  }

  const handleClose = () => {
    setSearchQuery('')
    setResults([])
    setNewStudent({ rut: '', name: '', email: '', phone: '' })
    onOpenChange(false)
  }

  return (
    <Dialog open={open} onOpenChange={handleClose}>
      <DialogContent className="sm:max-w-3xl">
        <DialogHeader>
          <DialogTitle>Añadir alumno al curso</DialogTitle>
          <DialogDescription>
            Busca una persona existente o crea un nuevo alumno
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-6">
          {/* Búsqueda de persona existente */}
          <div className="space-y-3">
            <h4 className="text-sm font-semibold">Buscar persona existente</h4>
            <div className="flex gap-2">
              <Input
                placeholder="Buscar por RUT o nombre..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
              />
              <Button onClick={handleSearch} disabled={searching}>
                {searching ? 'Buscando...' : 'Buscar'}
              </Button>
            </div>

            {results.length > 0 && (
              <div className="max-h-60 space-y-2 overflow-y-auto rounded-lg border p-3">
                {results.map((person) => (
                  <div
                    key={person.id}
                    className="flex items-center justify-between rounded-md border p-3 hover:bg-muted/40"
                  >
                    <div className="flex-1">
                      <div className="font-medium">{person.name}</div>
                      <div className="text-xs text-muted-foreground">
                        {person.rut} · {person.status}
                      </div>
                      {person.memberships.length > 0 && (
                        <div className="mt-1 flex flex-wrap gap-1">
                          {person.memberships.map((m, idx) => (
                            <Badge key={idx} variant="outline" className="text-xs">
                              {m.role} · {m.organization.name}
                            </Badge>
                          ))}
                        </div>
                      )}
                    </div>
                    <Button size="sm" onClick={() => handleAttach(person.id)}>
                      Añadir
                    </Button>
                  </div>
                ))}
              </div>
            )}

            {searchQuery && results.length === 0 && !searching && (
              <p className="text-sm text-muted-foreground">
                No se encontraron resultados. Puedes crear un nuevo alumno abajo.
              </p>
            )}
          </div>

          <div className="border-t pt-6">
            <h4 className="mb-3 text-sm font-semibold">Crear nuevo alumno</h4>
            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-2">
                <label className="text-sm font-medium">RUT *</label>
                <Input
                  placeholder="12345678-9"
                  value={newStudent.rut}
                  onChange={(e) => setNewStudent({ ...newStudent, rut: e.target.value })}
                />
              </div>
              <div className="space-y-2">
                <label className="text-sm font-medium">Nombre completo *</label>
                <Input
                  placeholder="Juan Pérez González"
                  value={newStudent.name}
                  onChange={(e) => setNewStudent({ ...newStudent, name: e.target.value })}
                />
              </div>
              <div className="space-y-2">
                <label className="text-sm font-medium">Email</label>
                <Input
                  type="email"
                  placeholder="juan@example.com"
                  value={newStudent.email}
                  onChange={(e) => setNewStudent({ ...newStudent, email: e.target.value })}
                />
              </div>
              <div className="space-y-2">
                <label className="text-sm font-medium">Teléfono</label>
                <Input
                  placeholder="+56912345678"
                  value={newStudent.phone}
                  onChange={(e) => setNewStudent({ ...newStudent, phone: e.target.value })}
                />
              </div>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" onClick={handleClose}>
            Cancelar
          </Button>
          <Button onClick={handleCreate} disabled={creating || !newStudent.rut || !newStudent.name}>
            {creating ? 'Creando...' : 'Crear y añadir'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
