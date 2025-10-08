<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Modelo para almacenar configuraciones de packages
 *
 * @property int $id
 * @property string $package_name
 * @property string $key
 * @property mixed $value
 * @property string $type
 * @property bool $encrypted
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PackageSetting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'package_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'package_name',
        'key',
        'value',
        'type',
        'encrypted',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'encrypted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the value attribute with automatic decryption
     */
    public function getValueAttribute($value): mixed
    {
        // Si está encriptado, desencriptar
        if ($this->encrypted && $value) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                // Si falla la desencriptación, retornar null
                return null;
            }
        }

        // Convertir según el tipo
        return $this->castValue($value, $this->type);
    }

    /**
     * Set the value attribute with automatic encryption
     */
    public function setValueAttribute($value): void
    {
        // Convertir a string para almacenar
        $stringValue = $this->valueToString($value);

        // Si debe estar encriptado, encriptar
        if ($this->encrypted && $stringValue) {
            $stringValue = Crypt::encryptString($stringValue);
        }

        $this->attributes['value'] = $stringValue;
    }

    /**
     * Convierte un valor según su tipo
     */
    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'number' => is_numeric($value) ? (int) $value : null,
            'float' => is_numeric($value) ? (float) $value : null,
            'array', 'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Convierte un valor a string para almacenar
     */
    private function valueToString(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    /**
     * Scope para filtrar por package
     */
    public function scopeForPackage($query, string $packageName)
    {
        return $query->where('package_name', $packageName);
    }

    /**
     * Scope para obtener un setting específico
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Get all settings for a package as key-value array
     */
    public static function getPackageSettings(string $packageName): array
    {
        return static::forPackage($packageName)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }
}
