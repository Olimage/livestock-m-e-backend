<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorDisagregation extends Model
{
    protected $fillable = [
        'indicator_id',
        'disagregation_item_id'
    ];

    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }

    public function disagregation()
    {
        return $this->belongsTo(DisagregationItem::class);
    }
}
