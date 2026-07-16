<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class IndicatorReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'indicator_type', 'indicator_id', 'indicator_code', 'department_id',
        'reporting_period_id', 'workflow_id', 'current_stage_id', 'target_value',
        'actual_value', 'narrative', 'status', 'created_by', 'submitted_at', 'published_at',
    ];

    protected $casts = [
        'status' => ReportStatus::class,
        'submitted_at' => 'datetime',
        'published_at' => 'datetime',
        'target_value' => 'decimal:4',
        'actual_value' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function indicator()
    {
        return $this->morphTo(__FUNCTION__, 'indicator_type', 'indicator_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function period()
    {
        return $this->belongsTo(ReportingPeriod::class, 'reporting_period_id');
    }

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function currentStage()
    {
        return $this->belongsTo(ApprovalWorkflowStage::class, 'current_stage_id');
    }

    public function values()
    {
        return $this->hasMany(IndicatorReportValue::class, 'report_id');
    }

    public function proofs()
    {
        return $this->hasMany(IndicatorReportProof::class, 'report_id');
    }

    public function approvals()
    {
        return $this->hasMany(IndicatorReportApproval::class, 'report_id')->orderBy('acted_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
