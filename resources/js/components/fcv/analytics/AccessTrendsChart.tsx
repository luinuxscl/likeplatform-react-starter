import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { useEffect, useState } from 'react'
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts'

interface DateRange {
  from: Date
  to: Date
}

interface TrendData {
  period: string
  count: number
}

interface AccessTrendsChartProps {
  dateRange: DateRange
  loading?: boolean
}

export function AccessTrendsChart({ dateRange, loading }: AccessTrendsChartProps) {
  const [data, setData] = useState<TrendData[]>([])

  useEffect(() => {
    fetchTrends()
  }, [dateRange])

  const fetchTrends = async () => {
    try {
      const params = new URLSearchParams({
        from: dateRange.from.toISOString().split('T')[0],
        to: dateRange.to.toISOString().split('T')[0],
        group_by: 'day',
      })

      const response = await fetch(`/fcv/analytics/trends?${params}`)
      const result = await response.json()
      setData(result.trends || [])
    } catch (error) {
      console.error('Error fetching trends:', error)
    }
  }

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Tendencias de Acceso</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-[300px] animate-pulse rounded bg-muted" />
        </CardContent>
      </Card>
    )
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Tendencias de Acceso</CardTitle>
      </CardHeader>
      <CardContent>
        <ResponsiveContainer width="100%" height={300}>
          <LineChart data={data}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis
              dataKey="period"
              tick={{ fontSize: 12 }}
              tickFormatter={(value) => {
                const date = new Date(value)
                return date.toLocaleDateString('es-CL', {
                  day: '2-digit',
                  month: 'short',
                })
              }}
            />
            <YAxis tick={{ fontSize: 12 }} />
            <Tooltip
              labelFormatter={(value) => {
                const date = new Date(value)
                return date.toLocaleDateString('es-CL', {
                  day: '2-digit',
                  month: 'long',
                  year: 'numeric',
                })
              }}
            />
            <Legend />
            <Line
              type="monotone"
              dataKey="count"
              stroke="hsl(var(--primary))"
              strokeWidth={2}
              name="Accesos"
              dot={{ r: 4 }}
              activeDot={{ r: 6 }}
            />
          </LineChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  )
}
