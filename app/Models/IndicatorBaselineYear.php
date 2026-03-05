<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorBaselineYear extends Model
{
    protected $fillable = [
        'indicator_id',
        'baseline_year',
        'target_year',
        'baseline',
        'target',
        'actual',
    ];

    protected $casts = [
        'baseline_year' => 'integer',
        'target_year'   => 'integer',
        'baseline'      => 'decimal:2',
        'target'        => 'decimal:2',
        'actual'        => 'decimal:2',
    ];

    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }
}
