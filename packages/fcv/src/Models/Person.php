<?php

namespace Like\Fcv\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Like\Fcv\Database\Factories\PersonFactory;

class Person extends Model
{
    use HasFactory;

    protected $table = 'fcv_persons';

    protected $fillable = [
        'rut',
        'name',
        'photo_path',
        'contact_info',
        'status',
    ];

    protected $casts = [
        'contact_info' => 'array',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'person_id');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'fcv_memberships', 'person_id', 'organization_id')
            ->withPivot(['role', 'start_date', 'end_date'])
            ->withTimestamps();
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'fcv_course_student', 'person_id', 'course_id')->withTimestamps();
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class, 'person_id');
    }

    protected static function newFactory(): PersonFactory
    {
        return PersonFactory::new();
    }
}
