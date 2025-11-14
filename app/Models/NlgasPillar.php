<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NlgasPillar extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description', 'baseline_year', 
        'target_year', 'source_document', 'responsible_entity'
    ];

    public function presidentialPriorities(): BelongsToMany
    {
        return $this->belongsToMany(PresidentialPriority::class, 'strategic_alignments')
                    ->withPivot(['sectoral_goal_id', 'bond_outcome_id', 'alignment_notes'])
                    ->withTimestamps();
    }

    public function sectoralGoals(): BelongsToMany
    {
        return $this->belongsToMany(SectoralGoal::class, 'strategic_alignments')
                    ->withPivot(['presidential_priority_id', 'bond_outcome_id', 'alignment_notes'])
                    ->withTimestamps();
    }

    public function bondOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(BondOutcome::class, 'strategic_alignments')
                    ->withPivot(['presidential_priority_id', 'sectoral_goal_id', 'alignment_notes'])
                    ->withTimestamps();
    }
}