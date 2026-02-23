<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisagregationItem extends Model
{
    protected $fillable = [
        'disagregation_category_id',
        'name'
    ];

    public function category()
    {
        return $this->belongsTo(DisagregationCategory::class);
    }

    public function indicatorDisagregation()
    {
        return $this->hasMany(IndicatorDisagregation::class);
    }
}
