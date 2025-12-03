<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SectoralGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description', 'baseline_year', 
        'target_year', 'source_document', 'responsible_entity', 'department_id'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class);
    }

    public function presidentialPriorities(): BelongsToMany
    {
        return $this->belongsToMany(PresidentialPriority::class, 'strategic_alignments')
                    ->withPivot(['bond_outcome_id', 'nlgas_pillar_id', 'alignment_notes'])
                    ->withTimestamps();
    }

    public function bondOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(BondOutcome::class, 'strategic_alignments')
                    ->withPivot(['presidential_priority_id', 'nlgas_pillar_id', 'alignment_notes'])
                    ->withTimestamps();
    }

    public function nlgasPillars(): BelongsToMany
    {
        return $this->belongsToMany(NlgasPillar::class, 'strategic_alignments')
                    ->withPivot(['presidential_priority_id', 'bond_outcome_id', 'alignment_notes'])
                    ->withTimestamps();
    }

    public function getResponsibleEntityAttribute(): ?string
    {
        if ($this->relationLoaded('department') || $this->department) {
            return $this->department?->name;
        }
        return $this->attributes['responsible_entity'] ?? null;
    }
}