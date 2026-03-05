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

    public function indicators()
    {
        return $this->belongsToMany(
            Indicator::class,
            'indicator_disagregations',
            'disagregation_item_id',
            'indicator_id'
        );
    }

    public function indicatorDisagregations()
    {
        return $this->hasMany(IndicatorDisagregation::class);
    }
}
