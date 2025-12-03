<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Indicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',

        'indicator_type',
        'measurement_unit',
        'baseline_value',
        'baseline_year',
        'target_value',
        'target_year',
        'data_source',
        'collection_frequency',
        'tier_level'
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'baseline_year' => 'integer',
        'target_year' => 'integer',
        'tier_level' => 'integer',
    ];


}
