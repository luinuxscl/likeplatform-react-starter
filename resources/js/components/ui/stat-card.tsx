import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { cn } from '@/lib/utils'
import { ArrowDownRight, ArrowUpRight } from 'lucide-react'

export type Trend = {
  direction: 'up' | 'down' | 'flat'
  value: string // e.g. +12.5% or -2.3%
  label?: string // e.g. vs last month
}

export type StatCardProps = {
  title: string
  value: string | number
  trend?: Trend
  helper?: string // secondary line under value
  className?: string
}

export function StatCard({ title, value, trend, helper, className }: StatCardProps) {
  const renderTrend = () => {
    if (!trend) return null
    const isUp = trend.direction === 'up'
    const isDown = trend.direction === 'down'
    const iconClass = 'h-3.5 w-3.5'
    return (
      <span className={cn(
        'inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-medium',
        isUp && 'bg-primary/10 text-primary',
        isDown && 'bg-destructive/10 text-destructive',
        trend.direction === 'flat' && 'bg-muted text-muted-foreground',
      )}>
        {isUp && <ArrowUpRight className={iconClass} />}
        {isDown && <ArrowDownRight className={iconClass} />}
        <span>{trend.value}</span>
      </span>
    )
  }

  return (
    <Card className={cn('rounded-xl border-border/80 shadow-sm', className)}>
      <CardHeader className="pb-2">
        <div className="flex items-center justify-between">
          <CardTitle className="text-xs font-medium text-muted-foreground">{title}</CardTitle>
          {renderTrend()}
        </div>
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-semibold text-foreground">{value}</div>
        {helper && (
          <div className="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
            {helper}
          </div>
        )}
      </CardContent>
    </Card>
  )
}
