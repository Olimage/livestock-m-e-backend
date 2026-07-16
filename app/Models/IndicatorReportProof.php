<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorReportProof extends Model
{
    protected $fillable = ['report_id', 'path', 'original_name', 'mime', 'size', 'uploaded_by'];

    public function report()
    {
        return $this->belongsTo(IndicatorReport::class, 'report_id');
    }
}
