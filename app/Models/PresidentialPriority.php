<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class PresidentialPriority extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description'
    ];

    public function tiers(): MorphToMany
    {
        return $this->morphToMany(Tier::class, 'tierable');
    }
}