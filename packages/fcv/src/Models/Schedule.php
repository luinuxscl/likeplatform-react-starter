<?php

namespace Like\Fcv\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'fcv_schedules';

    protected $fillable = [
        'scheduleable_type',
        'scheduleable_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    public function scheduleable(): MorphTo
    {
        return $this->morphTo();
    }
}
