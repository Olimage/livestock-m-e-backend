<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'nlgas_pillar_id',
        'department_id',
        'baseline_year',
        'target_year',
        'budget_allocation',
        'priority_level',
        'source_document'
    ];

    protected $casts = [
        'budget_allocation' => 'decimal:2',
        'baseline_year' => 'integer',
        'target_year' => 'integer',
    ];

    public function nlgasPillar(): BelongsTo
    {
        return $this->belongsTo(NlgasPillar::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class);
    }
}
