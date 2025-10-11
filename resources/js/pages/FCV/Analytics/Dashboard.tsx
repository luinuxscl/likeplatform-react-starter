import AppLayout from '@/layouts/app-layout'
import { Head } from '@inertiajs/react'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Button } from '@/components/ui/button'
import { Calendar, Download, RefreshCw, TrendingUp, Users, XCircle } from 'lucide-react'
import { useState, useEffect } from 'react'
import { DateRangePicker } from '@/components/fcv/analytics/DateRangePicker'
import { StatsCards } from '@/components/fcv/analytics/StatsCards'
import { AccessTrendsChart } from '@/components/fcv/analytics/AccessTrendsChart'
import { HourlyDistributionChart } from '@/components/fcv/analytics/HourlyDistributionChart'
import { WeekdayDistributionChart } from '@/components/fcv/analytics/WeekdayDistributionChart'
import { TopPersonsTable } from '@/components/fcv/analytics/TopPersonsTable'
import { DeniedReasonsChart } from '@/components/fcv/analytics/DeniedReasonsChart'

interface DateRange {
  from: Date
  to: Date
}

interface AnalyticsData {
  access_log_stats: {
    total: number
    allowed: number
    denied: number
    entries: number
    exits: number
    allowed_percentage: number
    denied_percentage: number
  }
  verification_stats: {
    total: number
    by_action: Record<string, number>
    top_users: Array<{
      user: { id: number; name: string; email: string } | null
      count: number
    }>
  }
  top_persons: Array<{
    person: { id: number; rut: string; name: string } | null
    access_count: number
  }>
  denied_reasons: Array<{
    value: string
    count: number
  }>
  hourly_distribution: Record<number, number>
  weekday_distribution: Record<string, number>
}

export default function AnalyticsDashboard() {
  const [dateRange, setDateRange] = useState<DateRange>({
    from: new Date(new Date().setDate(new Date().getDate() - 30)),
    to: new Date(),
  })
  const [data, setData] = useState<AnalyticsData | null>(null)
  const [loading, setLoading] = useState(false)
  const [autoRefresh, setAutoRefresh] = useState(false)

  const fetchData = async () => {
    setLoading(true)
    try {
      const params = new URLSearchParams({
        from: dateRange.from.toISOString().split('T')[0],
        to: dateRange.to.toISOString().split('T')[0],
      })

      const response = await fetch(`/fcv/analytics/complete-report?${params}`, {
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const result = await response.json()
      setData(result)
    } catch (error) {
      console.error('Error fetching analytics:', error)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchData()
  }, [dateRange])

  useEffect(() => {
    if (!autoRefresh) return

    const interval = setInterval(() => {
      fetchData()
    }, 30000) // Refresh cada 30 segundos

    return () => clearInterval(interval)
  }, [autoRefresh, dateRange])

  const handleExport = () => {
    // TODO: Implementar exportación
    console.log('Exportar datos', data)
  }

  return (
    <AppLayout>
      <Head title="Analytics - FCV" />

      <div className="space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Analytics</h1>
            <p className="text-muted-foreground">
              Estadísticas y reportes de accesos
            </p>
          </div>

          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => setAutoRefresh(!autoRefresh)}
            >
              <RefreshCw
                className={`mr-2 h-4 w-4 ${autoRefresh ? 'animate-spin' : ''}`}
              />
              {autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh OFF'}
            </Button>

            <Button variant="outline" size="sm" onClick={handleExport}>
              <Download className="mr-2 h-4 w-4" />
              Exportar
            </Button>

            <DateRangePicker
              dateRange={dateRange}
              onChange={setDateRange}
            />
          </div>
        </div>

        {/* Stats Cards */}
        {data && <StatsCards data={data.access_log_stats} loading={loading} />}

        {/* Charts */}
        <Tabs defaultValue="trends" className="space-y-4">
          <TabsList>
            <TabsTrigger value="trends">
              <TrendingUp className="mr-2 h-4 w-4" />
              Tendencias
            </TabsTrigger>
            <TabsTrigger value="distribution">
              <Calendar className="mr-2 h-4 w-4" />
              Distribución
            </TabsTrigger>
            <TabsTrigger value="people">
              <Users className="mr-2 h-4 w-4" />
              Personas
            </TabsTrigger>
            <TabsTrigger value="denied">
              <XCircle className="mr-2 h-4 w-4" />
              Denegados
            </TabsTrigger>
          </TabsList>

          <TabsContent value="trends" className="space-y-4">
            <AccessTrendsChart dateRange={dateRange} loading={loading} />
          </TabsContent>

          <TabsContent value="distribution" className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
              {data && (
                <>
                  <HourlyDistributionChart
                    data={data.hourly_distribution}
                    loading={loading}
                  />
                  <WeekdayDistributionChart
                    data={data.weekday_distribution}
                    loading={loading}
                  />
                </>
              )}
            </div>
          </TabsContent>

          <TabsContent value="people" className="space-y-4">
            {data && (
              <TopPersonsTable data={data.top_persons} loading={loading} />
            )}
          </TabsContent>

          <TabsContent value="denied" className="space-y-4">
            {data && (
              <DeniedReasonsChart
                data={data.denied_reasons}
                loading={loading}
              />
            )}
          </TabsContent>
        </Tabs>
      </div>
    </AppLayout>
  )
}
