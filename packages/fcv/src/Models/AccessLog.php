<?php

namespace Like\Fcv\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
