<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'description',
        'last_used_ip',
        'last_used_user_agent',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'abilities' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tokenable_id')->where('tokenable_type', User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('tokenable_type', $user->getMorphClass())->where('tokenable_id', $user->getKey());
    }
}
