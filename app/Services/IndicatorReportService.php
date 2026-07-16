<?php

namespace App\Services;

use App\Enums\ApprovalAction;
use App\Enums\ReportStatus;
use App\Enums\ResubmitBehavior;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStage;
use App\Models\IndicatorReport;
use App\Models\IndicatorReportProof;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class IndicatorReportService
{
    public function __construct(
        private readonly WorkflowService $workflows,
        private readonly SettingService $settings,
        private readonly ApprovalTrailService $trail,
    ) {}

    public function canReport(User $user, Model $indicator): bool
    {
        if ($user->is_admin) {
            return true;
        }

        $deptIds = $user->departments()->pluck('departments.id')->all();

        if (in_array($indicator->department_id, $deptIds, true)) {
            return true;
        }

        if (! $this->settings->get(SettingService::ALLOW_SUPPORTING_DEPT, false)) {
            return false;
        }

        if (! method_exists($indicator, 'supportingDepartments')) {
            return false;
        }

        $supportIds = $indicator->supportingDepartments()->pluck('departments.id')->all();

        return count(array_intersect($deptIds, $supportIds)) > 0;
    }

    public function create(User $user, array $data): IndicatorReport
    {
        return DB::transaction(function () use ($user, $data) {
            $indicator = ($data['indicator_type'])::findOrFail($data['indicator_id']);

            $report = IndicatorReport::create([
                'indicator_type' => $data['indicator_type'],
                'indicator_id' => $data['indicator_id'],
                'indicator_code' => $indicator->code ?? null,
                'department_id' => $data['department_id'],
                'reporting_period_id' => $data['reporting_period_id'],
                'target_value' => $data['target_value'] ?? null,
                'actual_value' => $data['actual_value'] ?? null,
                'narrative' => $data['narrative'] ?? null,
                'status' => ReportStatus::Draft,
                'created_by' => $user->id,
            ]);

            $this->syncValues($report, $data['values'] ?? []);

            return $report->load('values');
        });
    }

    public function update(IndicatorReport $report, array $data): IndicatorReport
    {
        return DB::transaction(function () use ($report, $data) {
            $report->fill(array_filter([
                'target_value' => $data['target_value'] ?? null,
                'actual_value' => $data['actual_value'] ?? null,
                'narrative' => $data['narrative'] ?? null,
                'reporting_period_id' => $data['reporting_period_id'] ?? null,
            ], fn ($v) => $v !== null))->save();

            if (array_key_exists('values', $data)) {
                $this->syncValues($report, $data['values']);
            }

            return $report->load('values');
        });
    }

    public function submit(User $user, IndicatorReport $report): IndicatorReport
    {
        return DB::transaction(function () use ($user, $report) {
            $workflow = $this->workflows->workflowForDepartment($report->department_id);
            abort_if(! $workflow, 422, 'No active approval workflow is assigned to this department.');

            $firstStage = $workflow->activeStages()->first();
            abort_if(! $firstStage, 422, 'The assigned workflow has no active stages.');

            $isResubmit = $report->status === ReportStatus::Returned;
            $targetStage = $this->resolveEntryStage($workflow, $report, $firstStage, $isResubmit);

            $report->forceFill([
                'workflow_id' => $workflow->id,
                'current_stage_id' => $targetStage->id,
                'status' => ReportStatus::Pending,
                'submitted_at' => now(),
            ])->save();

            $this->trail->record(
                $report,
                $user,
                $isResubmit ? ApprovalAction::Resubmitted : ApprovalAction::Submitted,
            );

            return $report->fresh(['currentStage']);
        });
    }

    /**
     * Decide which stage a (re)submitted report enters.
     *
     * New submissions and workflows configured `from_start` enter stage 1.
     * A resubmit of a returned report under `from_declined_stage` resumes at the
     * stage that declined it (falling back to stage 1 if that stage is gone).
     */
    private function resolveEntryStage(
        ApprovalWorkflow $workflow,
        IndicatorReport $report,
        ApprovalWorkflowStage $firstStage,
        bool $isResubmit,
    ): ApprovalWorkflowStage {
        if (! $isResubmit || $workflow->resubmit_behavior !== ResubmitBehavior::FromDeclinedStage->value) {
            return $firstStage;
        }

        $declinedStageId = $report->approvals()
            ->where('action', ApprovalAction::Declined->value)
            ->reorder('acted_at', 'desc')
            ->value('stage_id');

        if (! $declinedStageId) {
            return $firstStage;
        }

        return $workflow->activeStages()->where('id', $declinedStageId)->first() ?? $firstStage;
    }

    public function addProof(User $user, IndicatorReport $report, UploadedFile $file): IndicatorReportProof
    {
        $path = $file->store("indicator-reports/{$report->id}/proofs");

        return $report->proofs()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $user->id,
        ]);
    }

    private function syncValues(IndicatorReport $report, array $values): void
    {
        $report->values()->delete();
        foreach ($values as $row) {
            $report->values()->create([
                'disagregation_item_id' => $row['disagregation_item_id'] ?? null,
                'value' => $row['value'] ?? null,
            ]);
        }
    }
}
