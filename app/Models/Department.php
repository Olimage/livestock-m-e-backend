<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_technical',
        'parent_id'
    ];

    protected $casts =[
        'is_technical' => 'boolean'
    ];

    public function users(){

        return $this->hasManyThrough( User::class, UserDepartment::class);
    }

    public function permissions()
{
    return $this->morphMany(Permission::class, 'callable');
}

/**
 * Get all sub-departments of this department
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function children()
{
    return $this->hasMany(Department::class, 'parent_id');
}

/**
 * Get all descendants (sub-departments and their sub-departments)
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function descendants()
{
    return $this->children()->with('descendants');
}

/**
 * Get the parent department
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function parent()
{
    return $this->belongsTo(Department::class, 'parent_id');
}

/**
 * Get all ancestors (parent and its parent)
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function ancestors()
{
    return $this->parent()->with('ancestors');
}

/**
 * Check if department is a root department (has no parent)
 *
 * @return bool
 */
public function isRoot()
{
    return is_null($this->parent_id);
}

/**
 * Check if department has children
 *
 * @return bool
 */
public function hasChildren()
{
    return $this->children()->count() > 0;
}

// Strategic alignment relationships
public function sectoralGoals()
{
    return $this->hasMany(SectoralGoal::class);
}

public function bondOutcomes()
{
    return $this->hasMany(BondOutcome::class);
}

public function nlgasPillars()
{
    return $this->hasMany(NlgasPillar::class);
}

public function programs()
{
    return $this->hasMany(Program::class);
}

public function indicators()
{
    return $this->hasMany(Indicator::class);
}

public function crossCuttingMetrics()
{
    return $this->hasMany(CrossCuttingMetric::class);
}

}
