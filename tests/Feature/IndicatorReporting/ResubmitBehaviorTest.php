<?php

namespace Tests\Feature\IndicatorReporting;

use App\Enums\ReportStatus;
use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\OutputIndicator;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Services\IndicatorReportService;
use App\Services\ReportApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResubmitBehaviorTest extends TestCase
{
    use RefreshDatabase;

    private function scenario(string $behavior): array
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $ps = Role::create(['name' => 'PS', 'slug' => 'permanent_secretary']);

        $wf = ApprovalWorkflow::create(['name' => 'WF', 'slug' => 'wf', 'is_active' => true, 'resubmit_behavior' => $behavior]);
        $s1 = $wf->stages()->create(['name' => 'Validate', 'position' => 1, 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any']);
        $s2 = $wf->stages()->create(['name' => 'Final', 'position' => 2, 'assignment_type' => 'role', 'role_id' => $ps->id, 'approval_mode' => 'any']);
        $wf->departments()->attach($dept->id);

        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = User::create(['full_name' => 'Dir', 'email' => 'dir@x.io', 'password' => 'secret123']);
        $director->departments()->attach($dept->id);
        $prsUser = User::create(['full_name' => 'PRS', 'email' => 'prs@x.io', 'password' => 'secret123']);
        $prsUser->roles()->attach($prs->id);
        $psUser = User::create(['full_name' => 'PS', 'email' => 'ps@x.io', 'password' => 'secret123']);
        $psUser->roles()->attach($ps->id);

        $reports = app(IndicatorReportService::class);
        $report = $reports->submit($director, $reports->create($director, [
            'indicator_type' => OutputIndicator::class, 'indicator_id' => $indicator->id,
            'department_id' => $dept->id, 'reporting_period_id' => $period->id, 'actual_value' => 80,
        ]));

        // Advance to stage 2, then decline there so the report is returned from the final stage.
        $approvals = app(ReportApprovalService::class);
        $approvals->approve($prsUser, $report->fresh());
        $approvals->decline($psUser, $report->fresh(), 'Recheck the totals');

        return compact('report', 'director', 's1', 's2');
    }

    public function test_from_declined_stage_resumes_at_the_stage_that_declined(): void
    {
        ['report' => $report, 'director' => $director, 's2' => $s2] = $this->scenario('from_declined_stage');
        $this->assertSame(ReportStatus::Returned, $report->fresh()->status);

        $resubmitted = app(IndicatorReportService::class)->submit($director, $report->fresh());

        $this->assertSame(ReportStatus::Pending, $resubmitted->status);
        $this->assertSame($s2->id, $resubmitted->current_stage_id, 'resubmit should resume at the declined (final) stage');
    }

    public function test_from_start_restarts_at_first_stage(): void
    {
        ['report' => $report, 'director' => $director, 's1' => $s1] = $this->scenario('from_start');

        $resubmitted = app(IndicatorReportService::class)->submit($director, $report->fresh());

        $this->assertSame($s1->id, $resubmitted->current_stage_id, 'resubmit should restart at stage 1');
    }
}
