import AppLayout from '@/layouts/app-layout'
import { Head, router, useForm } from '@inertiajs/react'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { ArrowLeft } from 'lucide-react'
import { FormEventHandler, useState } from 'react'

interface Person {
  id: number
  rut: string
  name: string
}

interface Props {
  persons?: Person[]
}

const reasonLabels: Record<string, string> = {
  medical: 'Médico',
  special_event: 'Evento Especial',
  maintenance: 'Mantenimiento',
  administrative: 'Administrativo',
  other: 'Otro',
}

export default function CreateException({ persons = [] }: Props) {
  const [searchTerm, setSearchTerm] = useState('')
  const { data, setData, post, processing, errors } = useForm({
    person_id: '',
    reason: '',
    description: '',
    valid_from: '',
    valid_until: '',
  })

  const filteredPersons = persons.filter(
    (person) =>
      person.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      person.rut.includes(searchTerm)
  )

  const handleSubmit: FormEventHandler = (e) => {
    e.preventDefault()
    post('/fcv/access-exceptions')
  }

  const handleCancel = () => {
    router.visit('/fcv/access-exceptions')
  }

  return (
    <AppLayout>
      <Head title="Nueva Excepción - FCV" />

      <div className="space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="sm" onClick={handleCancel}>
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">
              Nueva Excepción de Acceso
            </h1>
            <p className="text-muted-foreground">
              Crear una nueva excepción temporal de acceso
            </p>
          </div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit}>
          <Card>
            <CardHeader>
              <CardTitle>Información de la Excepción</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Person Selection */}
              <div className="space-y-2">
                <Label htmlFor="person_id">
                  Persona <span className="text-destructive">*</span>
                </Label>
                <div className="space-y-2">
                  <Input
                    placeholder="Buscar por nombre o RUT..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                  />
                  <Select
                    value={data.person_id}
                    onValueChange={(value) => setData('person_id', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccionar persona" />
                    </SelectTrigger>
                    <SelectContent>
                      {filteredPersons.length === 0 ? (
                        <div className="p-2 text-sm text-muted-foreground">
                          No se encontraron personas
                        </div>
                      ) : (
                        filteredPersons.slice(0, 50).map((person) => (
                          <SelectItem key={person.id} value={person.id.toString()}>
                            {person.name} - {person.rut}
                          </SelectItem>
                        ))
                      )}
                    </SelectContent>
                  </Select>
                </div>
                {errors.person_id && (
                  <p className="text-sm text-destructive">{errors.person_id}</p>
                )}
              </div>

              {/* Reason */}
              <div className="space-y-2">
                <Label htmlFor="reason">
                  Razón <span className="text-destructive">*</span>
                </Label>
                <Select
                  value={data.reason}
                  onValueChange={(value) => setData('reason', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Seleccionar razón" />
                  </SelectTrigger>
                  <SelectContent>
                    {Object.entries(reasonLabels).map(([value, label]) => (
                      <SelectItem key={value} value={value}>
                        {label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.reason && (
                  <p className="text-sm text-destructive">{errors.reason}</p>
                )}
              </div>

              {/* Description */}
              <div className="space-y-2">
                <Label htmlFor="description">
                  Descripción <span className="text-destructive">*</span>
                </Label>
                <Textarea
                  id="description"
                  placeholder="Describe el motivo de la excepción..."
                  value={data.description}
                  onChange={(e) => setData('description', e.target.value)}
                  rows={4}
                />
                {errors.description && (
                  <p className="text-sm text-destructive">{errors.description}</p>
                )}
              </div>

              {/* Date Range */}
              <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="valid_from">
                    Válido desde <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="valid_from"
                    type="date"
                    value={data.valid_from}
                    onChange={(e) => setData('valid_from', e.target.value)}
                  />
                  {errors.valid_from && (
                    <p className="text-sm text-destructive">{errors.valid_from}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="valid_until">
                    Válido hasta <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="valid_until"
                    type="date"
                    value={data.valid_until}
                    onChange={(e) => setData('valid_until', e.target.value)}
                  />
                  {errors.valid_until && (
                    <p className="text-sm text-destructive">{errors.valid_until}</p>
                  )}
                </div>
              </div>

              {/* Actions */}
              <div className="flex justify-end gap-4">
                <Button
                  type="button"
                  variant="outline"
                  onClick={handleCancel}
                  disabled={processing}
                >
                  Cancelar
                </Button>
                <Button type="submit" disabled={processing}>
                  {processing ? 'Creando...' : 'Crear Excepción'}
                </Button>
              </div>
            </CardContent>
          </Card>
        </form>
      </div>
    </AppLayout>
  )
}
