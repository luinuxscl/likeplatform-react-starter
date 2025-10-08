import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Code, Check, AlertCircle } from 'lucide-react';
import { useState, useEffect } from 'react';

interface JsonFieldProps {
  name: string;
  label: string;
  value: any; // JSON object or array
  description?: string;
  placeholder?: string;
  help?: string;
  error?: string | null;
  rows?: number;
  onChange: (value: any) => void;
  className?: string;
}

export function JsonField({ name, label, value, description, placeholder, help, error, rows = 8, onChange, className }: JsonFieldProps) {
  const [textValue, setTextValue] = useState<string>('');
  const [isValid, setIsValid] = useState<boolean>(true);
  const [validationError, setValidationError] = useState<string | null>(null);

  // Initialize text value from JSON
  useEffect(() => {
    try {
      const formatted = JSON.stringify(value, null, 2);
      setTextValue(formatted);
      setIsValid(true);
      setValidationError(null);
    } catch (e) {
      setTextValue('{}');
      setIsValid(false);
      setValidationError('Invalid JSON');
    }
  }, [value]);

  const handleChange = (text: string) => {
    setTextValue(text);
    
    // Try to parse JSON
    try {
      const parsed = JSON.parse(text);
      setIsValid(true);
      setValidationError(null);
      onChange(parsed);
    } catch (e) {
      setIsValid(false);
      setValidationError((e as Error).message);
    }
  };

  const handleFormat = () => {
    try {
      const parsed = JSON.parse(textValue);
      const formatted = JSON.stringify(parsed, null, 2);
      setTextValue(formatted);
      setIsValid(true);
      setValidationError(null);
      onChange(parsed);
    } catch (e) {
      // Keep current value if can't parse
    }
  };

  return (
    <div className={cn('space-y-2', className)}>
      <div className="flex items-center justify-between">
        <Label htmlFor={name} className="flex items-center gap-2">
          <Code className="h-4 w-4" />
          {label}
        </Label>
        {error && <span className="text-xs text-destructive">{error}</span>}
      </div>
      {description && <p className="text-sm text-muted-foreground">{description}</p>}
      
      <div className="relative">
        <Textarea
          id={name}
          name={name}
          value={textValue}
          placeholder={placeholder || '{\n  "key": "value"\n}'}
          rows={rows}
          onChange={(e) => handleChange(e.target.value)}
          className={cn(
            'font-mono text-sm',
            !isValid && 'border-destructive focus-visible:ring-destructive'
          )}
        />
        
        <div className="absolute top-2 right-2 flex items-center gap-2">
          {isValid ? (
            <div className="flex items-center gap-1 text-xs text-green-600 bg-green-50 dark:bg-green-950 px-2 py-1 rounded">
              <Check className="h-3 w-3" />
              Valid
            </div>
          ) : (
            <div className="flex items-center gap-1 text-xs text-destructive bg-destructive/10 px-2 py-1 rounded">
              <AlertCircle className="h-3 w-3" />
              Invalid
            </div>
          )}
        </div>
      </div>

      <div className="flex items-center justify-between">
        <div className="flex-1">
          {validationError && (
            <p className="text-xs text-destructive">{validationError}</p>
          )}
          {help && !validationError && (
            <p className="text-xs text-muted-foreground">{help}</p>
          )}
        </div>
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={handleFormat}
          disabled={!isValid}
          className="text-xs"
        >
          Format JSON
        </Button>
      </div>
    </div>
  );
}
