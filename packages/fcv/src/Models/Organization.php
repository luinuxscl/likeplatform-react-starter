<?php

namespace Like\Fcv\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'fcv_organizations';

    protected $fillable = [
        'name',
        'acronym',
        'type',
        'description',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'organization_id');
    }

    public function persons(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'fcv_memberships', 'organization_id', 'person_id')
            ->withPivot(['role', 'start_date', 'end_date'])
            ->withTimestamps();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'organization_id');
    }
}
