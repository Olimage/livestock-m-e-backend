<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Indicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'sectoral_goal_id',
        'bond_outcome_id',
        'nlgas_pillar_id',
        'presidential_priority_id',
        'department_id',
        'indicator_type',
        'measurement_unit',
        'baseline_value',
        'baseline_year',
        'target_value',
        'target_year',
        'data_source',
        'collection_frequency',
        'responsible_entity',
        'tier_level'
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'baseline_year' => 'integer',
        'target_year' => 'integer',
        'tier_level' => 'integer',
    ];

    public function presidentialPriority(): BelongsTo
    {
        return $this->belongsTo(PresidentialPriority::class);
    }

    public function sectoralGoal(): BelongsTo
    {
        return $this->belongsTo(SectoralGoal::class);
    }

    public function bondOutcome(): BelongsTo
    {
        return $this->belongsTo(BondOutcome::class);
    }

    public function nlgasPillar(): BelongsTo
    {
        return $this->belongsTo(NlgasPillar::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
