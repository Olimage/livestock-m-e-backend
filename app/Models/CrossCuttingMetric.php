<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrossCuttingMetric extends Model
{
    protected $fillable = [
        'code',
        'area',
        'key_metric',
        'purpose',
    ];
}
