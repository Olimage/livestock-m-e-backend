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
        'reporting_period_id', 'baseline', 'baseline_year', 'workflow_id', 'current_stage_id',
        'target_value', 'actual_value', 'narrative', 'status', 'created_by', 'submitted_at',
        'published_at',
    ];

    protected $casts = [
        'status' => ReportStatus::class,
        'submitted_at' => 'datetime',
        'published_at' => 'datetime',
        'baseline' => 'decimal:4',
        'baseline_year' => 'integer',
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

    /**
     * Resolve by uuid (canonical) or by primary key.
     *
     * The API responses expose both `uuid` and `id`, and mne_frontend addresses
     * reports by `id`. Accepting either keeps both callers working without
     * making `id` the route key.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        // Constrain exactly one column. PostgreSQL will not compare a `uuid`
        // column against a non-uuid literal — `where uuid = 227` or
        // `where uuid = 'not-a-uuid'` raises SQLSTATE[22P02] rather than simply
        // not matching. (SQLite coerces silently, so tests never saw it.)
        if (ctype_digit((string) $value)) {
            return $this->newQuery()->whereKey((int) $value)->first();
        }

        return Str::isUuid($value)
            ? $this->newQuery()->where('uuid', $value)->first()
            : null;
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
