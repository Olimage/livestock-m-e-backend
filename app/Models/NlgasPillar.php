<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NlgasPillar extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description',
    ];

    /**
     * Get the programs associated with this pillar
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
