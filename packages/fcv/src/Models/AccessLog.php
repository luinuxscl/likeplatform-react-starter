<?php

namespace Like\Fcv\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AccessLog extends Model
{
    use HasFactory;

    protected $table = 'fcv_access_logs';

    protected $fillable = [
        'person_id',
        'vehicle_id',
        'occurred_at',
        'direction',
        'status',
        'reason',
        'gatekeeper_id',
        'meta',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'meta' => 'array',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function gatekeeper(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'gatekeeper_id');
    }

    // Scopes
    public function scopeAllowed(Builder $query): Builder
    {
        return $query->where('status', 'permitido');
    }

    public function scopeDenied(Builder $query): Builder
    {
        return $query->where('status', 'denegado');
    }

    public function scopeBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('occurred_at', [$from, $to]);
    }

    public function scopeEntry(Builder $query): Builder
    {
        return $query->where('direction', 'entrada');
    }

    public function scopeExit(Builder $query): Builder
    {
        return $query->where('direction', 'salida');
    }

    public function scopeByPerson(Builder $query, int $personId): Builder
    {
        return $query->where('person_id', $personId);
    }

    // Helper methods
    public function isAllowed(): bool
    {
        return $this->status === 'permitido';
    }

    public function isDenied(): bool
    {
        return $this->status === 'denegado';
    }

    public function isEntry(): bool
    {
        return $this->direction === 'entrada';
    }

    public function isExit(): bool
    {
        return $this->direction === 'salida';
    }
}
