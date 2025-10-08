import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';

interface SelectFieldProps {
  name: string;
  label: string;
  value: string;
  options: Record<string, string>;
  description?: string;
  help?: string;
  error?: string | null;
  onChange: (value: string) => void;
  className?: string;
}

export function SelectField({ name, label, value, options, description, help, error, onChange, className }: SelectFieldProps) {
  return (
    <div className={cn('space-y-2', className)}>
      <div className="flex items-center justify-between">
        <Label htmlFor={name}>{label}</Label>
        {error && <span className="text-xs text-destructive">{error}</span>}
      </div>
      {description && <p className="text-sm text-muted-foreground">{description}</p>}
      <Select value={value} onValueChange={onChange}>
        <SelectTrigger id={name}>
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          {Object.entries(options).map(([val, label]) => (
            <SelectItem key={val} value={val}>
              {label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
      {help && <p className="text-xs text-muted-foreground">{help}</p>}
    </div>
  );
}
