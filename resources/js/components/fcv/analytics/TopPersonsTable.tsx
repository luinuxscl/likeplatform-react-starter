import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { User } from 'lucide-react'

interface TopPerson {
  person: {
    id: number
    rut: string
    name: string
  } | null
  access_count: number
}

interface TopPersonsTableProps {
  data: TopPerson[]
  loading?: boolean
}

export function TopPersonsTable({ data, loading }: TopPersonsTableProps) {
  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Top Personas con Más Accesos</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-[400px] animate-pulse rounded bg-muted" />
        </CardContent>
      </Card>
    )
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Top Personas con Más Accesos</CardTitle>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="w-12">#</TableHead>
              <TableHead>Persona</TableHead>
              <TableHead>RUT</TableHead>
              <TableHead className="text-right">Accesos</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {data.length === 0 ? (
              <TableRow>
                <TableCell colSpan={4} className="text-center text-muted-foreground">
                  No hay datos disponibles
                </TableCell>
              </TableRow>
            ) : (
              data.map((item, index) => (
                <TableRow key={item.person?.id || index}>
                  <TableCell>
                    <Badge variant={index < 3 ? 'default' : 'secondary'}>
                      {index + 1}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <div className="flex h-8 w-8 items-center justify-center rounded-full bg-muted">
                        <User className="h-4 w-4" />
                      </div>
                      <span className="font-medium">
                        {item.person?.name || 'Desconocido'}
                      </span>
                    </div>
                  </TableCell>
                  <TableCell className="font-mono text-sm">
                    {item.person?.rut || '-'}
                  </TableCell>
                  <TableCell className="text-right font-semibold">
                    {item.access_count.toLocaleString()}
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  )
}
