<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para almacenar la configuración de widgets por usuario
 *
 * @property int $id
 * @property int $user_id
 * @property string $widget_key
 * @property int $position
 * @property string $size
 * @property array|null $config
 * @property bool $visible
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User $user
 */
class UserWidget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'widget_key',
        'position',
        'size',
        'config',
        'visible',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'visible' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para obtener widgets visibles
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /**
     * Scope para obtener widgets de un usuario
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para ordenar por posición
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Obtiene el layout de widgets para un usuario
     *
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLayoutForUser(int $userId)
    {
        return static::forUser($userId)
            ->visible()
            ->ordered()
            ->get();
    }

    /**
     * Guarda el layout completo de un usuario
     *
     * @param  int  $userId
     * @param  array  $widgets
     */
    public static function saveLayoutForUser(int $userId, array $widgets): void
    {
        foreach ($widgets as $index => $widget) {
            static::updateOrCreate(
                [
                    'user_id' => $userId,
                    'widget_key' => $widget['key'],
                ],
                [
                    'position' => $index,
                    'size' => $widget['size'] ?? 'col-span-12',
                    'config' => $widget['config'] ?? null,
                    'visible' => $widget['visible'] ?? true,
                ]
            );
        }
    }

    /**
     * Resetea el layout de un usuario (elimina todas las personalizaciones)
     *
     * @param  int  $userId
     */
    public static function resetLayoutForUser(int $userId): void
    {
        static::where('user_id', $userId)->delete();
    }

    /**
     * Toggle de visibilidad de un widget
     *
     * @param  int  $userId
     * @param  string  $widgetKey
     */
    public static function toggleVisibility(int $userId, string $widgetKey): bool
    {
        $widget = static::firstOrCreate(
            [
                'user_id' => $userId,
                'widget_key' => $widgetKey,
            ],
            [
                'visible' => true,
            ]
        );

        $widget->visible = !$widget->visible;
        $widget->save();

        return $widget->visible;
    }
}
