<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorBaselineYear extends Model
{
    protected $fillable = [
        'indicatorable_id',
        'indicatorable_type',
        'baseline_year',
        'target_year',
        'baseline',
        'target',
        'actual',
    ];

    protected $casts = [
        'baseline_year' => 'integer',
        'target_year' => 'integer',
        'baseline' => 'decimal:2',
        'target' => 'decimal:2',
        'actual' => 'decimal:2',
    ];

    public function indicatorable()
    {
        return $this->morphTo();
    }
}
