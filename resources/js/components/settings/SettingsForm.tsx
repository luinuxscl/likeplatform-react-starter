import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { FieldRenderer } from './FieldRenderer';
import type { PackageSettings, FieldSchema } from '@/types/settings';
import { useMemo } from 'react';

interface SettingsFormProps {
  pkg: PackageSettings;
}

export function SettingsForm({ pkg }: SettingsFormProps) {
  const { data, setData, put, processing, errors, reset } = useForm<{ settings: Record<string, any> }>(
    {
      settings: pkg.settings ?? {},
    }
  );

  const fieldsBySection = useMemo(() => {
    const entries = Object.entries(pkg.schema.schema ?? {}) as [string, FieldSchema][];
    const map: Record<string, [string, FieldSchema][]> = {};
    for (const pair of entries) {
      const sectionKey = pair[1].section || 'general';
      if (!map[sectionKey]) map[sectionKey] = [];
      map[sectionKey].push(pair);
    }
    return map;
  }, [pkg.schema.schema]);

  const onChange = (name: string, value: any) => {
    setData('settings', {
      ...data.settings,
      [name]: value,
    });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.package-settings.update', { package: pkg.name }), {
      preserveScroll: true,
    });
  };

  const handleReset = () => {
    // POST to reset
    window.axios
      .post(route('admin.package-settings.reset', { package: pkg.name }))
      .then(() => {
        // After reset, reload page via Inertia to fetch defaults again
        window.location.reload();
      });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="flex items-center gap-3">
        <Button type="submit" disabled={processing}>
          {processing ? 'Saving…' : 'Save changes'}
        </Button>
        <Button type="button" variant="secondary" onClick={handleReset} disabled={processing}>
          Reset to defaults
        </Button>
      </div>

      <Separator />

      <div className="space-y-8">
        {Object.entries(pkg.schema.sections || { general: 'General' }).map(([sectionKey, sectionLabel]) => {
          const sectionFields = fieldsBySection[sectionKey] ?? [];
          if (sectionFields.length === 0) return null;

          return (
            <section key={sectionKey} className="space-y-4">
              <h3 className="text-lg font-semibold">{sectionLabel}</h3>
              <div className="grid gap-4">
                {sectionFields.map(([key, field]) => (
                  <FieldRenderer
                    key={key}
                    name={key}
                    field={field}
                    value={data.settings[key]}
                    onChange={onChange}
                    error={(errors as any)[`settings.${key}`] as string | undefined}
                  />
                ))}
              </div>
            </section>
          );
        })}
      </div>

      <Separator />

      <div className="flex items-center gap-3">
        <Button type="submit" disabled={processing}>
          {processing ? 'Saving…' : 'Save changes'}
        </Button>
        <Button type="button" variant="secondary" onClick={handleReset} disabled={processing}>
          Reset to defaults
        </Button>
      </div>
    </form>
  );
}
