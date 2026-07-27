<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorReportProof extends Model
{
    protected $fillable = ['report_id', 'path', 'original_name', 'mime', 'size', 'uploaded_by'];

    /**
     * `path` is an internal location on the private disk. It is never a URL and
     * must not reach clients — evidence is served only through
     * Api\IndicatorReportController::downloadProof, which authorizes the request.
     */
    protected $hidden = ['path'];

    public function report()
    {
        return $this->belongsTo(IndicatorReport::class, 'report_id');
    }
}
