<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategicAlignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'presidential_priority_id', 'sectoral_goal_id', 
        'bond_outcome_id', 'nlgas_pillar_id', 'alignment_notes'
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
}