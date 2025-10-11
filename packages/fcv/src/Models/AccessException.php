<?php

namespace Like\Fcv\Models;

use App\Models\User;
use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Like\Fcv\Database\Factories\AccessExceptionFactory;

class AccessException extends Model
{
    use HasFactory, HasAuditLogs;

    protected static function newFactory(): AccessExceptionFactory
    {
        return AccessExceptionFactory::new();
    }

    protected $table = 'fcv_access_exceptions';

    protected $fillable = [
        'person_id',
        'reason',
        'description',
        'valid_from',
        'valid_until',
        'created_by',
        'approved_by',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    // Relaciones
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();
        return $query->where('status', 'approved')
            ->where('valid_from', '<=', $now)
            ->where('valid_until', '>=', $now);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForPerson(Builder $query, int $personId): Builder
    {
        return $query->where('person_id', $personId);
    }

    public function scopeValidAt(Builder $query, Carbon $dateTime): Builder
    {
        return $query->where('valid_from', '<=', $dateTime)
            ->where('valid_until', '>=', $dateTime);
    }

    // Helper methods
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->status === 'approved'
            && $this->valid_from <= $now
            && $this->valid_until >= $now;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->valid_until < Carbon::now();
    }

    public function approve(User $user): bool
    {
        $this->status = 'approved';
        $this->approved_by = $user->id;
        return $this->save();
    }

    public function reject(User $user, string $reason): bool
    {
        $this->status = 'rejected';
        $this->approved_by = $user->id;
        $this->rejection_reason = $reason;
        return $this->save();
    }

    public function getReasonLabel(): string
    {
        return match ($this->reason) {
            'medical' => 'Médico',
            'special_event' => 'Evento Especial',
            'maintenance' => 'Mantenimiento',
            'administrative' => 'Administrativo',
            'other' => 'Otro',
            default => 'Desconocido',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            default => 'Desconocido',
        };
    }
}
