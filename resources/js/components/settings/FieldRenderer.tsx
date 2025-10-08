import { cn } from '@/lib/utils';
import type { FieldSchema } from '@/types/settings';
import { TextField } from './fields/TextField';
import { NumberField } from './fields/NumberField';
import { BooleanField } from './fields/BooleanField';
import { SelectField } from './fields/SelectField';
import { ColorField } from './fields/ColorField';

export interface FieldRendererProps {
  name: string;
  field: FieldSchema;
  value: any;
  onChange: (name: string, value: any) => void;
  error?: string | null;
  className?: string;
}

export function FieldRenderer({ name, field, value, onChange, error, className }: FieldRendererProps) {
  const common = { name, label: field.label, description: field.description, placeholder: field.placeholder, help: field.help, error };

  switch (field.type) {
    case 'text':
    case 'textarea':
      return (
        <TextField
          {...common}
          value={value ?? ''}
          textarea={field.type === 'textarea'}
          rows={field.rows}
          onChange={(v) => onChange(name, v)}
          className={className}
        />
      );
    case 'number':
      return (
        <NumberField
          {...common}
          value={value ?? field.default ?? 0}
          min={field.min}
          max={field.max}
          step={field.step}
          onChange={(v) => onChange(name, v)}
          className={className}
        />
      );
    case 'boolean':
      return (
        <BooleanField
          {...common}
          checked={Boolean(value ?? field.default ?? false)}
          onChange={(v) => onChange(name, v)}
          className={className}
        />
      );
    case 'select':
      return (
        <SelectField
          {...common}
          value={value ?? field.default ?? ''}
          options={field.options || {}}
          onChange={(v) => onChange(name, v)}
          className={className}
        />
      );
    case 'color':
      return (
        <ColorField
          {...common}
          value={value ?? field.default ?? '#000000'}
          onChange={(v) => onChange(name, v)}
          className={className}
        />
      );
    default:
      return (
        <div className={cn('text-sm text-muted-foreground', className)}>
          Unsupported field type: {field.type}
        </div>
      );
  }
}
