<?php

namespace App\Services;

use App\Enums\ApprovalAction;
use App\Enums\ReportStatus;
use App\Enums\StageApprovalMode;
use App\Enums\StageAssignmentType;
use App\Models\ApprovalWorkflowStage;
use App\Models\IndicatorReport;
use App\Models\IndicatorReportApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportApprovalService
{
    public function __construct(private readonly ApprovalTrailService $trail) {}

    public function isEligibleApprover(User $user, IndicatorReport $report): bool
    {
        if ($user->is_admin) {
            return true;
        }
        $stage = $report->currentStage;
        if (! $stage) {
            return false;
        }

        return $this->eligibleUserIds($stage)->contains($user->id);
    }

    public function approve(User $user, IndicatorReport $report): IndicatorReport
    {
        return DB::transaction(function () use ($user, $report) {
            $stage = $report->currentStage;
            abort_if(! $stage || $report->status !== ReportStatus::Pending, 422, 'Report is not awaiting approval.');
            abort_unless($this->isEligibleApprover($user, $report), 403, 'You may not approve at this stage.');

            $this->trail->record($report, $user, ApprovalAction::Approved, $stage);

            if (! $this->stageSatisfied($report, $stage)) {
                return $report->fresh(['currentStage']);
            }

            $next = $report->workflow->activeStages()->where('position', '>', $stage->position)->first();

            if ($next) {
                $report->forceFill(['current_stage_id' => $next->id])->save();
            } else {
                $report->forceFill([
                    'current_stage_id' => null,
                    'status' => ReportStatus::Approved,
                    'published_at' => now(),
                ])->save();

                $this->trail->record($report->fresh(), $user, ApprovalAction::Published, $stage);
            }

            return $report->fresh(['currentStage']);
        });
    }

    public function decline(User $user, IndicatorReport $report, string $reason): IndicatorReport
    {
        return DB::transaction(function () use ($user, $report, $reason) {
            $stage = $report->currentStage;
            abort_if(! $stage || $report->status !== ReportStatus::Pending, 422, 'Report is not awaiting approval.');
            abort_unless($this->isEligibleApprover($user, $report), 403, 'You may not act at this stage.');

            $this->trail->record($report, $user, ApprovalAction::Declined, $stage, $reason);

            $report->forceFill([
                'current_stage_id' => null,
                'status' => ReportStatus::Returned,
            ])->save();

            $this->trail->record($report->fresh(), $user, ApprovalAction::Returned, $stage, $reason);

            return $report->fresh();
        });
    }

    private function stageSatisfied(IndicatorReport $report, ApprovalWorkflowStage $stage): bool
    {
        if ($stage->approval_mode === StageApprovalMode::Any->value || $stage->approval_mode === StageApprovalMode::Any) {
            return true;
        }

        $required = $this->eligibleUserIds($stage);
        if ($required->isEmpty()) {
            return true;
        }

        // Approvals recorded for this stage since the report last (re)entered it.
        $enteredAt = $this->stageEnteredAt($report, $stage);
        $approverIds = IndicatorReportApproval::where('report_id', $report->id)
            ->where('stage_id', $stage->id)
            ->where('action', ApprovalAction::Approved->value)
            ->where('acted_at', '>=', $enteredAt)
            ->pluck('actor_id')
            ->unique();

        return $required->diff($approverIds)->isEmpty();
    }

    private function stageEnteredAt(IndicatorReport $report, ApprovalWorkflowStage $stage): \Illuminate\Support\Carbon
    {
        // The most recent submit/resubmit/prior-stage-approval that moved the report into this stage.
        $marker = IndicatorReportApproval::where('report_id', $report->id)
            ->whereIn('action', [
                ApprovalAction::Submitted->value,
                ApprovalAction::Resubmitted->value,
                ApprovalAction::Approved->value,
            ])
            ->orderByDesc('acted_at')
            ->get()
            ->first(fn ($a) => $a->stage_id === null || $a->stage_id < $stage->id);

        return $marker?->acted_at ?? $report->submitted_at ?? $report->created_at;
    }

    private function eligibleUserIds(ApprovalWorkflowStage $stage): \Illuminate\Support\Collection
    {
        if ($stage->assignment_type === StageAssignmentType::Users->value) {
            return $stage->users()->pluck('users.id');
        }

        if (! $stage->role_id) {
            return collect();
        }

        return User::whereHas('roles', fn ($q) => $q->where('roles.id', $stage->role_id))->pluck('id');
    }
}
