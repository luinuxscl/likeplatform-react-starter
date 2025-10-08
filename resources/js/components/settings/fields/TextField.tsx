import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';

interface TextFieldProps {
  name: string;
  label: string;
  value: string;
  description?: string;
  placeholder?: string;
  help?: string;
  error?: string | null;
  textarea?: boolean;
  rows?: number;
  onChange: (value: string) => void;
  className?: string;
}

export function TextField({ name, label, value, description, placeholder, help, error, textarea = false, rows = 4, onChange, className }: TextFieldProps) {
  return (
    <div className={cn('space-y-2', className)}>
      <div className="flex items-center justify-between">
        <Label htmlFor={name}>{label}</Label>
        {error && <span className="text-xs text-destructive">{error}</span>}
      </div>
      {description && <p className="text-sm text-muted-foreground">{description}</p>}
      {textarea ? (
        <Textarea id={name} name={name} value={value} rows={rows} placeholder={placeholder} onChange={(e) => onChange(e.target.value)} />
      ) : (
        <Input id={name} name={name} value={value} placeholder={placeholder} onChange={(e) => onChange(e.target.value)} />
      )}
      {help && <p className="text-xs text-muted-foreground">{help}</p>}
    </div>
  );
}
