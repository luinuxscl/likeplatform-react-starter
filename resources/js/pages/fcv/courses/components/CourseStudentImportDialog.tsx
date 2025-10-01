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

type ImportResult = {
  created: number
  updated: number
  attached: number
  errors: Array<{ row: number; message: string }>
}

type Props = {
  courseId: number
  open: boolean
  onOpenChange: (open: boolean) => void
}

export default function CourseStudentImportDialog({ courseId, open, onOpenChange }: Props) {
  const [file, setFile] = useState<File | null>(null)
  const [importing, setImporting] = useState(false)
  const [result, setResult] = useState<ImportResult | null>(null)

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = event.target.files?.[0]
    if (selectedFile) {
      setFile(selectedFile)
      setResult(null)
    }
  }

  const handleImport = async () => {
    if (!file) return

    setImporting(true)
    setResult(null)

    const formData = new FormData()
    formData.append('file', file)

    try {
      const response = await fetch(`/fcv/courses/${courseId}/students/import`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: formData,
        credentials: 'same-origin',
      })

      if (!response.ok) {
        throw new Error('Error al importar el archivo')
      }

      const data = await response.json()
      setResult(data.summary)

      // Si no hay errores, recargar la página después de 2 segundos
      if (data.summary.errors.length === 0) {
        setTimeout(() => {
          router.reload()
          onOpenChange(false)
        }, 2000)
      }
    } catch (error) {
      console.error('Error importing:', error)
      alert('Error al importar el archivo. Por favor, intenta nuevamente.')
    } finally {
      setImporting(false)
    }
  }

  const handleClose = () => {
    setFile(null)
    setResult(null)
    onOpenChange(false)
  }

  const handleDownloadErrors = () => {
    if (!result || result.errors.length === 0) return

    const csv = [
      'Fila,Error',
      ...result.errors.map((error) => `${error.row},"${error.message}"`),
    ].join('\n')

    const blob = new Blob([csv], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'errores-importacion.csv'
    a.click()
    URL.revokeObjectURL(url)
  }

  return (
    <Dialog open={open} onOpenChange={handleClose}>
      <DialogContent className="sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle>Importar alumnos desde Excel</DialogTitle>
          <DialogDescription>
            Sube un archivo Excel (.xlsx, .xls) o CSV con las columnas: RUT, Nombre, Email (opcional), Teléfono
            (opcional)
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          {!result ? (
            <>
              <div className="space-y-2">
                <label className="text-sm font-medium">Archivo</label>
                <Input
                  type="file"
                  accept=".xlsx,.xls,.csv"
                  onChange={handleFileChange}
                  disabled={importing}
                />
                {file && (
                  <p className="text-xs text-muted-foreground">
                    Archivo seleccionado: {file.name} ({(file.size / 1024).toFixed(2)} KB)
                  </p>
                )}
              </div>

              <div className="rounded-lg border bg-muted/30 p-4">
                <h4 className="mb-2 text-sm font-semibold">Formato esperado:</h4>
                <div className="overflow-x-auto">
                  <table className="min-w-full text-xs">
                    <thead>
                      <tr className="border-b">
                        <th className="px-2 py-1 text-left font-medium">RUT</th>
                        <th className="px-2 py-1 text-left font-medium">Nombre</th>
                        <th className="px-2 py-1 text-left font-medium">Email</th>
                        <th className="px-2 py-1 text-left font-medium">Teléfono</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td className="px-2 py-1">12345678-9</td>
                        <td className="px-2 py-1">Juan Pérez</td>
                        <td className="px-2 py-1">juan@example.com</td>
                        <td className="px-2 py-1">+56912345678</td>
                      </tr>
                      <tr>
                        <td className="px-2 py-1">98765432-1</td>
                        <td className="px-2 py-1">María López</td>
                        <td className="px-2 py-1">maria@example.com</td>
                        <td className="px-2 py-1">+56987654321</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p className="mt-2 text-xs text-muted-foreground">
                  Las columnas pueden tener nombres alternativos: RUT/RUN, Nombre/Name, Email/Correo,
                  Teléfono/Phone/Celular
                </p>
              </div>
            </>
          ) : (
            <div className="space-y-4">
              <div className="rounded-lg border p-4">
                <h4 className="mb-3 text-sm font-semibold">Resumen de importación</h4>
                <div className="grid gap-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Personas creadas:</span>
                    <span className="font-medium">{result.created}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Personas actualizadas:</span>
                    <span className="font-medium">{result.updated}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Alumnos vinculados:</span>
                    <span className="font-medium">{result.attached}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Errores:</span>
                    <span className={`font-medium ${result.errors.length > 0 ? 'text-destructive' : ''}`}>
                      {result.errors.length}
                    </span>
                  </div>
                </div>
              </div>

              {result.errors.length > 0 && (
                <div className="rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                  <div className="mb-2 flex items-center justify-between">
                    <h4 className="text-sm font-semibold text-destructive">Errores encontrados</h4>
                    <Button size="sm" variant="outline" onClick={handleDownloadErrors}>
                      Descargar errores
                    </Button>
                  </div>
                  <div className="max-h-40 space-y-1 overflow-y-auto text-xs">
                    {result.errors.slice(0, 10).map((error, index) => (
                      <div key={index} className="text-muted-foreground">
                        <span className="font-medium">Fila {error.row}:</span> {error.message}
                      </div>
                    ))}
                    {result.errors.length > 10 && (
                      <p className="text-muted-foreground">
                        ... y {result.errors.length - 10} errores más (descarga el archivo para ver todos)
                      </p>
                    )}
                  </div>
                </div>
              )}

              {result.errors.length === 0 && (
                <div className="rounded-lg border border-green-600/50 bg-green-600/10 p-4 text-sm text-green-600">
                  ✓ Importación completada exitosamente. Recargando página...
                </div>
              )}
            </div>
          )}
        </div>

        <DialogFooter>
          {!result ? (
            <>
              <Button variant="outline" onClick={handleClose} disabled={importing}>
                Cancelar
              </Button>
              <Button onClick={handleImport} disabled={!file || importing}>
                {importing ? 'Importando...' : 'Importar'}
              </Button>
            </>
          ) : (
            <Button onClick={handleClose}>Cerrar</Button>
          )}
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
