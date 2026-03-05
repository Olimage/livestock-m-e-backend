<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisagregationCategory extends Model
{
    protected $fillable = [
        'name'
    ];

    public function items()
    {
        return $this->hasMany(DisagregationItem::class);
    }

    public function indicatorLinks()
    {
        return $this->hasManyThrough(
            IndicatorDisagregation::class,
            DisagregationItem::class,
            'disagregation_category_id', // FK on disagregation_items → categories
            'disagregation_item_id',      // FK on indicator_disagregations → items
            'id',
            'id'
        );
    }
}
