import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { Calendar } from 'lucide-react';

interface DateFieldProps {
  name: string;
  label: string;
  value: string; // ISO date string (YYYY-MM-DD)
  description?: string;
  placeholder?: string;
  help?: string;
  error?: string | null;
  min?: string;
  max?: string;
  onChange: (value: string) => void;
  className?: string;
}

export function DateField({ name, label, value, description, placeholder, help, error, min, max, onChange, className }: DateFieldProps) {
  return (
    <div className={cn('space-y-2', className)}>
      <div className="flex items-center justify-between">
        <Label htmlFor={name}>{label}</Label>
        {error && <span className="text-xs text-destructive">{error}</span>}
      </div>
      {description && <p className="text-sm text-muted-foreground">{description}</p>}
      <div className="relative">
        <Input
          id={name}
          name={name}
          type="date"
          value={value}
          placeholder={placeholder}
          min={min}
          max={max}
          onChange={(e) => onChange(e.target.value)}
          className="pr-10"
        />
        <Calendar className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
      </div>
      {help && <p className="text-xs text-muted-foreground">{help}</p>}
    </div>
  );
}
