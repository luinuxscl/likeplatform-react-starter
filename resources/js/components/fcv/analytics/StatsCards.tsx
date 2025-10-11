import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Activity,
  ArrowDownCircle,
  ArrowUpCircle,
  CheckCircle,
  XCircle,
} from 'lucide-react'

interface StatsData {
  total: number
  allowed: number
  denied: number
  entries: number
  exits: number
  allowed_percentage: number
  denied_percentage: number
}

interface StatsCardsProps {
  data: StatsData
  loading?: boolean
}

export function StatsCards({ data, loading }: StatsCardsProps) {
  const stats = [
    {
      title: 'Total Accesos',
      value: data.total.toLocaleString(),
      icon: Activity,
      description: 'Registros totales',
      color: 'text-blue-600',
      bgColor: 'bg-blue-100',
    },
    {
      title: 'Permitidos',
      value: data.allowed.toLocaleString(),
      icon: CheckCircle,
      description: `${data.allowed_percentage.toFixed(1)}% del total`,
      color: 'text-green-600',
      bgColor: 'bg-green-100',
    },
    {
      title: 'Denegados',
      value: data.denied.toLocaleString(),
      icon: XCircle,
      description: `${data.denied_percentage.toFixed(1)}% del total`,
      color: 'text-red-600',
      bgColor: 'bg-red-100',
    },
    {
      title: 'Entradas',
      value: data.entries.toLocaleString(),
      icon: ArrowDownCircle,
      description: 'Accesos de entrada',
      color: 'text-purple-600',
      bgColor: 'bg-purple-100',
    },
    {
      title: 'Salidas',
      value: data.exits.toLocaleString(),
      icon: ArrowUpCircle,
      description: 'Accesos de salida',
      color: 'text-orange-600',
      bgColor: 'bg-orange-100',
    },
  ]

  return (
    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
      {stats.map((stat) => {
        const Icon = stat.icon
        return (
          <Card key={stat.title}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">
                {stat.title}
              </CardTitle>
              <div className={`rounded-full p-2 ${stat.bgColor}`}>
                <Icon className={`h-4 w-4 ${stat.color}`} />
              </div>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {loading ? (
                  <div className="h-8 w-20 animate-pulse rounded bg-muted" />
                ) : (
                  stat.value
                )}
              </div>
              <p className="text-xs text-muted-foreground">
                {stat.description}
              </p>
            </CardContent>
          </Card>
        )
      })}
    </div>
  )
}
