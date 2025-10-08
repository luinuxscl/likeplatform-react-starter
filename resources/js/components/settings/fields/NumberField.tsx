import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

interface NumberFieldProps {
  name: string;
  label: string;
  value: number;
  description?: string;
  placeholder?: string;
  help?: string;
  error?: string | null;
  min?: number;
  max?: number;
  step?: number;
  onChange: (value: number) => void;
  className?: string;
}

export function NumberField({ name, label, value, description, placeholder, help, error, min, max, step, onChange, className }: NumberFieldProps) {
  return (
    <div className={cn('space-y-2', className)}>
      <div className="flex items-center justify-between">
        <Label htmlFor={name}>{label}</Label>
        {error && <span className="text-xs text-destructive">{error}</span>}
      </div>
      {description && <p className="text-sm text-muted-foreground">{description}</p>}
      <Input
        id={name}
        name={name}
        type="number"
        value={value}
        placeholder={placeholder}
        min={min}
        max={max}
        step={step}
        onChange={(e) => onChange(e.target.value === '' ? 0 : Number(e.target.value))}
      />
      {help && <p className="text-xs text-muted-foreground">{help}</p>}
    </div>
  );
}
