import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

interface ColorFieldProps {
  name: string;
  label: string;
  value: string; // hex color
  description?: string;
  help?: string;
  error?: string | null;
  onChange: (value: string) => void;
  className?: string;
}

export function ColorField({ name, label, value, description, help, error, onChange, className }: ColorFieldProps) {
  // Ensure value is a valid hex color; fallback to #000000
  const color = /^#([0-9A-Fa-f]{3}){1,2}$/.test(value || '') ? value : '#000000';

  return (
    <div className={cn('space-y-2', className)}>
      <div className="flex items-center justify-between">
        <Label htmlFor={name}>{label}</Label>
        {error && <span className="text-xs text-destructive">{error}</span>}
      </div>
      {description && <p className="text-sm text-muted-foreground">{description}</p>}
      <div className="flex items-center gap-3">
        <Input
          id={name}
          name={name}
          type="color"
          value={color}
          onChange={(e) => onChange(e.target.value)}
          className="w-12 p-1 h-9"
        />
        <Input
          type="text"
          value={value}
          onChange={(e) => onChange(e.target.value)}
          placeholder="#000000"
          className="flex-1"
        />
      </div>
      {help && <p className="text-xs text-muted-foreground">{help}</p>}
    </div>
  );
}
