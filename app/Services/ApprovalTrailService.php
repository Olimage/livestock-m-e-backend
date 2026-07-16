<?php

namespace App\Services;

use App\Enums\ApprovalAction;
use App\Models\ApprovalWorkflowStage;
use App\Models\IndicatorReport;
use App\Models\IndicatorReportApproval;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ApprovalTrailService
{
    public function record(
        IndicatorReport $report,
        User $actor,
        ApprovalAction $action,
        ?ApprovalWorkflowStage $stage = null,
        ?string $reason = null,
    ): IndicatorReportApproval {
        return IndicatorReportApproval::create([
            'report_id' => $report->id,
            'stage_id' => $stage?->id,
            'actor_id' => $actor->id,
            'action' => $action,
            'reason' => $reason,
            'snapshot' => [
                'status' => $report->status->value,
                'current_stage_id' => $report->current_stage_id,
                'target_value' => $report->target_value,
                'actual_value' => $report->actual_value,
            ],
            'acted_at' => now(),
        ]);
    }

    public function forReport(IndicatorReport $report): Collection
    {
        return $report->approvals()->with(['actor:id,full_name', 'stage:id,name'])->get();
    }

    public function all(array $filters = []): LengthAwarePaginator
    {
        return IndicatorReportApproval::with(['actor:id,full_name', 'stage:id,name', 'report:id,uuid,indicator_code'])
            ->when($filters['report_id'] ?? null, fn ($q, $id) => $q->where('report_id', $id))
            ->when($filters['action'] ?? null, fn ($q, $a) => $q->where('action', $a))
            ->orderByDesc('acted_at')
            ->paginate($filters['per_page'] ?? 25);
    }
}
