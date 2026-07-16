<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorReportValue extends Model
{
    protected $fillable = ['report_id', 'disagregation_item_id', 'value'];

    protected $casts = ['value' => 'decimal:4'];

    public function report()
    {
        return $this->belongsTo(IndicatorReport::class, 'report_id');
    }

    public function disagregationItem()
    {
        return $this->belongsTo(DisagregationItem::class, 'disagregation_item_id');
    }
}
