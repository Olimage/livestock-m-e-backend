<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;


class NlgasPillar extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description'
    ];

    /**
     * Get the pillar programs associated with this pillar
     */
    public function pillarPrograms(): HasMany
    {
        return $this->hasMany(Program::class);
    }

        public function tiers(): MorphToMany
    {
        return $this->morphToMany(Tier::class, 'tierable');
    }

}
