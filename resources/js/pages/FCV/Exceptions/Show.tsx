import AppLayout from '@/layouts/app-layout'
import { Head, router } from '@inertiajs/react'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import {
  ArrowLeft,
  CheckCircle,
  XCircle,
  Clock,
  User,
  Calendar,
  FileText,
  AlertCircle,
} from 'lucide-react'

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
  approved_at: string | null
  rejection_reason: string | null
  created_at: string
  updated_at: string
}

interface Props {
  exception: AccessException
}

const statusConfig = {
  pending: {
    label: 'Pendiente',
    variant: 'secondary' as const,
    icon: Clock,
    color: 'text-yellow-600',
    bgColor: 'bg-yellow-50',
  },
  approved: {
    label: 'Aprobado',
    variant: 'default' as const,
    icon: CheckCircle,
    color: 'text-green-600',
    bgColor: 'bg-green-50',
  },
  rejected: {
    label: 'Rechazado',
    variant: 'destructive' as const,
    icon: XCircle,
    color: 'text-red-600',
    bgColor: 'bg-red-50',
  },
}

const reasonLabels: Record<string, string> = {
  medical: 'Médico',
  special_event: 'Evento Especial',
  maintenance: 'Mantenimiento',
  administrative: 'Administrativo',
  other: 'Otro',
}

export default function ShowException({ exception }: Props) {
  const StatusIcon = statusConfig[exception.status].icon

  const isActive = () => {
    const now = new Date()
    const from = new Date(exception.valid_from)
    const until = new Date(exception.valid_until)
    return exception.status === 'approved' && from <= now && until >= now
  }

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-CL', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    })
  }

  const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('es-CL', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  }

  const handleBack = () => {
    router.visit('/fcv/access-exceptions')
  }

  const handleEdit = () => {
    router.visit(`/fcv/access-exceptions/${exception.id}/edit`)
  }

  const handleApprove = () => {
    if (confirm('¿Aprobar esta excepción?')) {
      router.post(`/fcv/access-exceptions/${exception.id}/approve`)
    }
  }

  const handleReject = () => {
    const reason = prompt('Razón del rechazo:')
    if (reason) {
      router.post(`/fcv/access-exceptions/${exception.id}/reject`, {
        rejection_reason: reason,
      })
    }
  }

  const handleDelete = () => {
    if (confirm('¿Estás seguro de eliminar esta excepción?')) {
      router.delete(`/fcv/access-exceptions/${exception.id}`)
    }
  }

  return (
    <AppLayout>
      <Head title={`Excepción #${exception.id} - FCV`} />

      <div className="space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="ghost" size="sm" onClick={handleBack}>
              <ArrowLeft className="h-4 w-4" />
            </Button>
            <div>
              <h1 className="text-3xl font-bold tracking-tight">
                Excepción de Acceso #{exception.id}
              </h1>
              <p className="text-muted-foreground">
                Detalle de la excepción de acceso
              </p>
            </div>
          </div>

          {/* Actions */}
          <div className="flex gap-2">
            {exception.status === 'pending' && (
              <>
                <Button variant="outline" onClick={handleEdit}>
                  Editar
                </Button>
                <Button variant="default" onClick={handleApprove}>
                  <CheckCircle className="mr-2 h-4 w-4" />
                  Aprobar
                </Button>
                <Button variant="destructive" onClick={handleReject}>
                  <XCircle className="mr-2 h-4 w-4" />
                  Rechazar
                </Button>
              </>
            )}
          </div>
        </div>

        <div className="grid gap-6 md:grid-cols-3">
          {/* Main Info */}
          <div className="md:col-span-2 space-y-6">
            {/* Status Card */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <StatusIcon className={`h-5 w-5 ${statusConfig[exception.status].color}`} />
                  Estado de la Excepción
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex items-center gap-4">
                  <Badge variant={statusConfig[exception.status].variant} className="text-lg py-2 px-4">
                    {statusConfig[exception.status].label}
                  </Badge>
                  {isActive() && (
                    <Badge variant="default" className="bg-blue-600">
                      Activa
                    </Badge>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Person Info */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <User className="h-5 w-5" />
                  Información de la Persona
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <div className="text-sm text-muted-foreground">Nombre</div>
                  <div className="text-lg font-medium">{exception.person.name}</div>
                </div>
                <Separator />
                <div>
                  <div className="text-sm text-muted-foreground">RUT</div>
                  <div className="text-lg font-medium">{exception.person.rut}</div>
                </div>
              </CardContent>
            </Card>

            {/* Exception Details */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <FileText className="h-5 w-5" />
                  Detalles de la Excepción
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <div className="text-sm text-muted-foreground">Razón</div>
                  <Badge variant="outline" className="mt-1">
                    {reasonLabels[exception.reason]}
                  </Badge>
                </div>
                <Separator />
                <div>
                  <div className="text-sm text-muted-foreground">Descripción</div>
                  <div className="mt-1 whitespace-pre-wrap">{exception.description}</div>
                </div>
              </CardContent>
            </Card>

            {/* Rejection Reason */}
            {exception.status === 'rejected' && exception.rejection_reason && (
              <Card className="border-destructive">
                <CardHeader>
                  <CardTitle className="flex items-center gap-2 text-destructive">
                    <AlertCircle className="h-5 w-5" />
                    Razón del Rechazo
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="whitespace-pre-wrap">{exception.rejection_reason}</div>
                </CardContent>
              </Card>
            )}
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Validity Period */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Calendar className="h-5 w-5" />
                  Período de Validez
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <div className="text-sm text-muted-foreground">Desde</div>
                  <div className="font-medium">{formatDate(exception.valid_from)}</div>
                </div>
                <Separator />
                <div>
                  <div className="text-sm text-muted-foreground">Hasta</div>
                  <div className="font-medium">{formatDate(exception.valid_until)}</div>
                </div>
              </CardContent>
            </Card>

            {/* Audit Trail */}
            <Card>
              <CardHeader>
                <CardTitle>Historial</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4 text-sm">
                <div>
                  <div className="text-muted-foreground">Creado por</div>
                  <div className="font-medium">{exception.creator.name}</div>
                  <div className="text-xs text-muted-foreground">
                    {formatDateTime(exception.created_at)}
                  </div>
                </div>

                {exception.approver && (
                  <>
                    <Separator />
                    <div>
                      <div className="text-muted-foreground">
                        {exception.status === 'approved' ? 'Aprobado' : 'Rechazado'} por
                      </div>
                      <div className="font-medium">{exception.approver.name}</div>
                      {exception.approved_at && (
                        <div className="text-xs text-muted-foreground">
                          {formatDateTime(exception.approved_at)}
                        </div>
                      )}
                    </div>
                  </>
                )}

                <Separator />
                <div>
                  <div className="text-muted-foreground">Última actualización</div>
                  <div className="text-xs text-muted-foreground">
                    {formatDateTime(exception.updated_at)}
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Danger Zone */}
            {exception.status === 'pending' && (
              <Card className="border-destructive">
                <CardHeader>
                  <CardTitle className="text-destructive">Zona de Peligro</CardTitle>
                </CardHeader>
                <CardContent>
                  <Button
                    variant="destructive"
                    className="w-full"
                    onClick={handleDelete}
                  >
                    Eliminar Excepción
                  </Button>
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  )
}
