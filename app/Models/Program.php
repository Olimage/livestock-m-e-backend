<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nlgas_pillar_id',
        'code',
        'title',
    ];

    protected $casts = [
        'baseline_year' => 'integer',
        'target_year' => 'integer',
    ];

    /**
     * Get the NLGAS Pillar that owns this program
     */
    public function nlgasPillar(): BelongsTo
    {
        return $this->belongsTo(NlgasPillar::class);
    }
}
