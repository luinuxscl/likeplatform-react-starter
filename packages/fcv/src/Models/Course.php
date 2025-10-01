<?php

namespace Like\Fcv\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'fcv_courses';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'description',
        'valid_from',
        'valid_until',
        'entry_tolerance_mode',
        'entry_tolerance_minutes',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'entry_tolerance_minutes' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'fcv_course_student', 'course_id', 'person_id')->withTimestamps();
    }

    public function schedules(): MorphMany
    {
        return $this->morphMany(Schedule::class, 'scheduleable');
    }
}
