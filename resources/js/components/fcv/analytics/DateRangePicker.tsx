import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Calendar } from 'lucide-react'

interface DateRange {
  from: Date
  to: Date
}

interface DateRangePickerProps {
  dateRange: DateRange
  onChange: (range: DateRange) => void
}

export function DateRangePicker({ dateRange, onChange }: DateRangePickerProps) {
  const presets = [
    {
      label: 'Hoy',
      getValue: () => ({
        from: new Date(),
        to: new Date(),
      }),
    },
    {
      label: 'Últimos 7 días',
      getValue: () => ({
        from: new Date(new Date().setDate(new Date().getDate() - 7)),
        to: new Date(),
      }),
    },
    {
      label: 'Últimos 30 días',
      getValue: () => ({
        from: new Date(new Date().setDate(new Date().getDate() - 30)),
        to: new Date(),
      }),
    },
    {
      label: 'Últimos 90 días',
      getValue: () => ({
        from: new Date(new Date().setDate(new Date().getDate() - 90)),
        to: new Date(),
      }),
    },
    {
      label: 'Este mes',
      getValue: () => ({
        from: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        to: new Date(),
      }),
    },
    {
      label: 'Mes anterior',
      getValue: () => {
        const now = new Date()
        const firstDayLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1)
        const lastDayLastMonth = new Date(now.getFullYear(), now.getMonth(), 0)
        return {
          from: firstDayLastMonth,
          to: lastDayLastMonth,
        }
      },
    },
  ]

  const formatDateRange = (range: DateRange) => {
    const from = range.from.toLocaleDateString('es-CL', {
      day: '2-digit',
      month: 'short',
    })
    const to = range.to.toLocaleDateString('es-CL', {
      day: '2-digit',
      month: 'short',
    })
    return `${from} - ${to}`
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="outline" size="sm">
          <Calendar className="mr-2 h-4 w-4" />
          {formatDateRange(dateRange)}
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-48">
        {presets.map((preset) => (
          <DropdownMenuItem
            key={preset.label}
            onClick={() => onChange(preset.getValue())}
          >
            {preset.label}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
