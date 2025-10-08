import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Upload, X, File } from 'lucide-react';
import { useState } from 'react';

interface FileFieldProps {
  name: string;
  label: string;
  value: string; // URL or file path
  description?: string;
  help?: string;
  error?: string | null;
  accept?: string; // e.g., "image/*" or ".pdf,.doc"
  onChange: (value: string) => void;
  className?: string;
}

export function FileField({ name, label, value, description, help, error, accept, onChange, className }: FileFieldProps) {
  const [fileName, setFileName] = useState<string | null>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setFileName(file.name);
      // In a real implementation, you would upload the file to the server
      // and get back a URL. For now, we'll just use the file name.
      onChange(file.name);
    }
  };

  const handleClear = () => {
    setFileName(null);
    onChange('');
  };

  return (
    <div className={cn('space-y-2', className)}>
      <div className="flex items-center justify-between">
        <Label htmlFor={name}>{label}</Label>
        {error && <span className="text-xs text-destructive">{error}</span>}
      </div>
      {description && <p className="text-sm text-muted-foreground">{description}</p>}
      
      <div className="flex items-center gap-2">
        {value || fileName ? (
          <div className="flex-1 flex items-center gap-2 px-3 py-2 border rounded-md bg-muted/50">
            <File className="h-4 w-4 text-muted-foreground" />
            <span className="text-sm flex-1 truncate">{fileName || value}</span>
            <Button
              type="button"
              variant="ghost"
              size="icon"
              className="h-6 w-6"
              onClick={handleClear}
            >
              <X className="h-3 w-3" />
            </Button>
          </div>
        ) : (
          <div className="flex-1">
            <Input
              id={name}
              name={name}
              type="file"
              accept={accept}
              onChange={handleFileChange}
              className="hidden"
            />
            <Label
              htmlFor={name}
              className="flex items-center justify-center gap-2 px-4 py-2 border rounded-md cursor-pointer hover:bg-muted/50 transition-colors"
            >
              <Upload className="h-4 w-4" />
              <span className="text-sm">Choose file</span>
            </Label>
          </div>
        )}
      </div>
      
      {help && <p className="text-xs text-muted-foreground">{help}</p>}
      {accept && (
        <p className="text-xs text-muted-foreground">
          Accepted formats: {accept}
        </p>
      )}
    </div>
  );
}
