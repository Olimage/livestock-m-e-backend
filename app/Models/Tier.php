<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tier extends Model
{
    protected $fillable = [
        'tier',
        'level',
        'measurement_frequency',
        'attribution',
    ];

    public function indicators(): MorphToMany
    {
        return $this->morphedByMany(Indicator::class, 'tierable');
    }

    public function sectoralGoals(): MorphToMany
    {
        return $this->morphedByMany(SectoralGoal::class, 'tierable');
    }

    public function presidentialPriorities(): MorphToMany
    {
        return $this->morphedByMany(PresidentialPriority::class, 'tierable');
    }

    public function bondOutcomes(): MorphToMany
    {
        return $this->morphedByMany(BondOutcome::class, 'tierable');
    }

    public function nlgasPillar(): MorphToMany
    {
        return $this->morphedByMany(NlgasPillar::class, 'tierable');
    }
}
