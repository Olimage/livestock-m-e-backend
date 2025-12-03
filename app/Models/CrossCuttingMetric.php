<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrossCuttingMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'department_id',
        'measurement_unit',
        'baseline_value',
        'baseline_year',
        'target_value',
        'target_year',
        'data_source',
        'collection_frequency',
        'responsible_entity',
        'metric_category'
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'baseline_year' => 'integer',
        'target_year' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
