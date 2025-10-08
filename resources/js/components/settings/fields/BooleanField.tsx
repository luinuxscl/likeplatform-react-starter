import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { cn } from '@/lib/utils';

interface BooleanFieldProps {
  name: string;
  label: string;
  checked: boolean;
  description?: string;
  help?: string;
  error?: string | null;
  onChange: (value: boolean) => void;
  className?: string;
}

export function BooleanField({ name, label, checked, description, help, error, onChange, className }: BooleanFieldProps) {
  return (
    <div className={cn('space-y-2', className)}>
      <div className="flex items-center justify-between">
        <Label htmlFor={name}>{label}</Label>
        {error && <span className="text-xs text-destructive">{error}</span>}
      </div>
      {description && <p className="text-sm text-muted-foreground">{description}</p>}
      <div className="flex items-center gap-3">
        <Switch id={name} checked={checked} onCheckedChange={onChange} />
        <span className="text-sm text-muted-foreground">{checked ? 'Enabled' : 'Disabled'}</span>
      </div>
      {help && <p className="text-xs text-muted-foreground">{help}</p>}
    </div>
  );
}
