<?php

namespace App\Services;

use App\Models\Option;
use Illuminate\Support\Facades\Cache;

class Options
{
    protected function cacheKey(string $key): string
    {
        return "option:".$key;
    }

    /**
     * Get an option value casted by its stored type.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever($this->cacheKey($key), function () use ($key, $default) {
            $opt = Option::where('key', $key)->first();
            if (! $opt) return $default;
            return $this->castFromType($opt->value, (string) $opt->type);
        });
    }

    /**
     * Set an option value and type, updating cache.
     */
    public function set(string $key, mixed $value, ?string $type = null, ?string $group = null, ?string $description = null): void
    {
        $stored = Option::updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->normalizeToString($value, $type),
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget($this->cacheKey($key));
        Cache::forever($this->cacheKey($key), $this->castFromType($stored->value, (string) $stored->type));
    }

    /** Forget a cached option value. */
    public function forget(string $key): void
    {
        Cache::forget($this->cacheKey($key));
    }

    /** Return all options as [key => value_casted]. */
    public function all(): array
    {
        $out = [];
        foreach (Option::all(['key','value','type']) as $opt) {
            $out[$opt->key] = $this->castFromType($opt->value, (string) $opt->type);
        }
        return $out;
    }

    protected function castFromType(?string $value, string $type): mixed
    {
        if ($value === null) return null;
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value, // string, text, url, date_format, timezone, etc
        };
    }

    protected function normalizeToString(mixed $value, ?string $type): ?string
    {
        if ($value === null) return null;
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) ((int) $value),
            'json' => json_encode($value),
            default => (string) $value,
        };
    }
}
