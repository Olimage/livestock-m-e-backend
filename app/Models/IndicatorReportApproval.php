<?php

namespace App\Models;

use App\Enums\ApprovalAction;
use Illuminate\Database\Eloquent\Model;

class IndicatorReportApproval extends Model
{
    protected $fillable = ['report_id', 'stage_id', 'actor_id', 'action', 'reason', 'snapshot', 'acted_at'];

    protected $casts = [
        'action' => ApprovalAction::class,
        'snapshot' => 'array',
        'acted_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(IndicatorReport::class, 'report_id');
    }

    public function stage()
    {
        return $this->belongsTo(ApprovalWorkflowStage::class, 'stage_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
