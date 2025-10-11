import AppLayout from '@/layouts/app-layout'
import { Head, router } from '@inertiajs/react'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Plus, MoreVertical, Search, Filter, CheckCircle, XCircle, Clock } from 'lucide-react'
import { useState } from 'react'

interface Person {
  id: number
  rut: string
  name: string
}

interface User {
  id: number
  name: string
}

interface AccessException {
  id: number
  person: Person
  reason: string
  description: string
  valid_from: string
  valid_until: string
  status: 'pending' | 'approved' | 'rejected'
  created_by: number
  creator: User
  approver: User | null
  rejection_reason: string | null
  created_at: string
}

interface PaginatedData {
  data: AccessException[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

interface Props {
  exceptions: PaginatedData
}

const statusConfig = {
  pending: {
    label: 'Pendiente',
    variant: 'secondary' as const,
    icon: Clock,
    color: 'text-yellow-600',
  },
  approved: {
    label: 'Aprobado',
    variant: 'default' as const,
    icon: CheckCircle,
    color: 'text-green-600',
  },
  rejected: {
    label: 'Rechazado',
    variant: 'destructive' as const,
    icon: XCircle,
    color: 'text-red-600',
  },
}

const reasonLabels: Record<string, string> = {
  medical: 'Médico',
  special_event: 'Evento Especial',
  maintenance: 'Mantenimiento',
  administrative: 'Administrativo',
  other: 'Otro',
}

export default function ExceptionsIndex({ exceptions }: Props) {
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState<string>('all')
  const [reasonFilter, setReasonFilter] = useState<string>('all')

  const handleCreate = () => {
    router.visit('/fcv/access-exceptions/create')
  }

  const handleView = (id: number) => {
    router.visit(`/fcv/access-exceptions/${id}`)
  }

  const handleEdit = (id: number) => {
    router.visit(`/fcv/access-exceptions/${id}/edit`)
  }

  const handleDelete = (id: number) => {
    if (confirm('¿Estás seguro de eliminar esta excepción?')) {
      router.delete(`/fcv/access-exceptions/${id}`)
    }
  }

  const handleApprove = (id: number) => {
    if (confirm('¿Aprobar esta excepción?')) {
      router.post(`/fcv/access-exceptions/${id}/approve`)
    }
  }

  const handleReject = (id: number) => {
    const reason = prompt('Razón del rechazo:')
    if (reason) {
      router.post(`/fcv/access-exceptions/${id}/reject`, {
        rejection_reason: reason,
      })
    }
  }

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-CL', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    })
  }

  const isActive = (exception: AccessException) => {
    const now = new Date()
    const from = new Date(exception.valid_from)
    const until = new Date(exception.valid_until)
    return exception.status === 'approved' && from <= now && until >= now
  }

  return (
    <AppLayout>
      <Head title="Excepciones de Acceso - FCV" />

      <div className="space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">
              Excepciones de Acceso
            </h1>
            <p className="text-muted-foreground">
              Gestión de excepciones temporales de acceso
            </p>
          </div>

          <Button onClick={handleCreate}>
            <Plus className="mr-2 h-4 w-4" />
            Nueva Excepción
          </Button>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Filter className="h-5 w-5" />
              Filtros
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-3">
              <div className="relative">
                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Buscar por nombre o RUT..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="pl-9"
                />
              </div>

              <Select value={statusFilter} onValueChange={setStatusFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Estado" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Todos los estados</SelectItem>
                  <SelectItem value="pending">Pendiente</SelectItem>
                  <SelectItem value="approved">Aprobado</SelectItem>
                  <SelectItem value="rejected">Rechazado</SelectItem>
                </SelectContent>
              </Select>

              <Select value={reasonFilter} onValueChange={setReasonFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Razón" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Todas las razones</SelectItem>
                  <SelectItem value="medical">Médico</SelectItem>
                  <SelectItem value="special_event">Evento Especial</SelectItem>
                  <SelectItem value="maintenance">Mantenimiento</SelectItem>
                  <SelectItem value="administrative">Administrativo</SelectItem>
                  <SelectItem value="other">Otro</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        {/* Table */}
        <Card>
          <CardContent className="p-0">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Persona</TableHead>
                  <TableHead>Razón</TableHead>
                  <TableHead>Período</TableHead>
                  <TableHead>Estado</TableHead>
                  <TableHead>Creado por</TableHead>
                  <TableHead className="text-right">Acciones</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {exceptions.data.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={6} className="text-center text-muted-foreground">
                      No hay excepciones registradas
                    </TableCell>
                  </TableRow>
                ) : (
                  exceptions.data.map((exception) => {
                    const StatusIcon = statusConfig[exception.status].icon
                    return (
                      <TableRow key={exception.id}>
                        <TableCell>
                          <div>
                            <div className="font-medium">{exception.person.name}</div>
                            <div className="text-sm text-muted-foreground">
                              {exception.person.rut}
                            </div>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline">
                            {reasonLabels[exception.reason]}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <div className="text-sm">
                            <div>{formatDate(exception.valid_from)}</div>
                            <div className="text-muted-foreground">
                              hasta {formatDate(exception.valid_until)}
                            </div>
                            {isActive(exception) && (
                              <Badge variant="default" className="mt-1">
                                Activa
                              </Badge>
                            )}
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <StatusIcon className={`h-4 w-4 ${statusConfig[exception.status].color}`} />
                            <Badge variant={statusConfig[exception.status].variant}>
                              {statusConfig[exception.status].label}
                            </Badge>
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="text-sm">{exception.creator.name}</div>
                        </TableCell>
                        <TableCell className="text-right">
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="sm">
                                <MoreVertical className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem onClick={() => handleView(exception.id)}>
                                Ver detalle
                              </DropdownMenuItem>
                              {exception.status === 'pending' && (
                                <>
                                  <DropdownMenuItem onClick={() => handleEdit(exception.id)}>
                                    Editar
                                  </DropdownMenuItem>
                                  <DropdownMenuItem onClick={() => handleApprove(exception.id)}>
                                    Aprobar
                                  </DropdownMenuItem>
                                  <DropdownMenuItem onClick={() => handleReject(exception.id)}>
                                    Rechazar
                                  </DropdownMenuItem>
                                  <DropdownMenuItem
                                    onClick={() => handleDelete(exception.id)}
                                    variant="destructive"
                                  >
                                    Eliminar
                                  </DropdownMenuItem>
                                </>
                              )}
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </TableCell>
                      </TableRow>
                    )
                  })
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        {/* Pagination */}
        {exceptions.last_page > 1 && (
          <div className="flex items-center justify-between">
            <div className="text-sm text-muted-foreground">
              Mostrando {exceptions.data.length} de {exceptions.total} excepciones
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                disabled={exceptions.current_page === 1}
                onClick={() => router.visit(`/fcv/access-exceptions?page=${exceptions.current_page - 1}`)}
              >
                Anterior
              </Button>
              <Button
                variant="outline"
                size="sm"
                disabled={exceptions.current_page === exceptions.last_page}
                onClick={() => router.visit(`/fcv/access-exceptions?page=${exceptions.current_page + 1}`)}
              >
                Siguiente
              </Button>
            </div>
          </div>
        )}
      </div>
    </AppLayout>
  )
}
