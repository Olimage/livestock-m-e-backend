<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SectoralGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description', 'baseline_year', 
        'target_year', 'source_document', 'responsible_entity'
    ];

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
}