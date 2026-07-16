<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportingPeriod extends Model
{
    protected $fillable = ['name', 'type', 'year', 'period_number', 'start_date', 'end_date', 'is_open'];

    protected $casts = [
        'is_open' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_open', true);
    }
}
