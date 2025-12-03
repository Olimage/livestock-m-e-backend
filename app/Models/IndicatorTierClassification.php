<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorTierClassification extends Model
{
    protected $fillable = [
        'tier',
        'level',
        'measurement_frequency',
        'attribution',
    ];
}
