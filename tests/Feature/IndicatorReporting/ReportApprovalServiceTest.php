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

class ReportApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    private array $ctx;

    private function boot(string $mode = 'any'): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $ps = Role::create(['name' => 'PS', 'slug' => 'permanent_secretary']);

        $wf = ApprovalWorkflow::create(['name' => 'WF', 'slug' => 'wf', 'is_active' => true, 'resubmit_behavior' => 'from_start']);
        $s1 = $wf->stages()->create(['name' => 'Validate', 'position' => 1, 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => $mode]);
        $s2 = $wf->stages()->create(['name' => 'Final', 'position' => 2, 'assignment_type' => 'role', 'role_id' => $ps->id, 'approval_mode' => 'any']);
        $wf->departments()->attach($dept->id);

        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = User::create(['full_name' => 'Dir', 'email' => 'dir@x.io', 'password' => 'secret123']);
        $director->departments()->attach($dept->id);

        $prsUser = User::create(['full_name' => 'PRS1', 'email' => 'prs1@x.io', 'password' => 'secret123']);
        $prsUser->roles()->attach($prs->id);
        $prsUser2 = User::create(['full_name' => 'PRS2', 'email' => 'prs2@x.io', 'password' => 'secret123']);
        $prsUser2->roles()->attach($prs->id);
        $psUser = User::create(['full_name' => 'PS1', 'email' => 'ps1@x.io', 'password' => 'secret123']);
        $psUser->roles()->attach($ps->id);

        $reports = app(IndicatorReportService::class);
        $report = $reports->create($director, [
            'indicator_type' => OutputIndicator::class, 'indicator_id' => $indicator->id,
            'department_id' => $dept->id, 'reporting_period_id' => $period->id,
            'target_value' => 100, 'actual_value' => 80,
        ]);
        $report = $reports->submit($director, $report);

        $this->ctx = compact('report', 'director', 'prsUser', 'prsUser2', 'psUser', 's1', 's2');
    }

    public function test_any_mode_advances_on_single_approval_then_publishes(): void
    {
        $this->boot('any');
        $service = app(ReportApprovalService::class);

        $r = $service->approve($this->ctx['prsUser'], $this->ctx['report']->fresh());
        $this->assertSame($this->ctx['s2']->id, $r->current_stage_id);
        $this->assertSame(ReportStatus::Pending, $r->status);

        $r = $service->approve($this->ctx['psUser'], $r->fresh());
        $this->assertSame(ReportStatus::Approved, $r->status);
        $this->assertNotNull($r->published_at);
        $this->assertDatabaseHas('indicator_report_approvals', ['report_id' => $r->id, 'action' => 'published']);
    }

    public function test_all_mode_requires_every_role_member(): void
    {
        $this->boot('all');
        $service = app(ReportApprovalService::class);

        $r = $service->approve($this->ctx['prsUser'], $this->ctx['report']->fresh());
        $this->assertSame($this->ctx['s1']->id, $r->current_stage_id, 'stays until all approve');

        $r = $service->approve($this->ctx['prsUser2'], $r->fresh());
        $this->assertSame($this->ctx['s2']->id, $r->current_stage_id, 'advances after all approve');
    }

    public function test_decline_returns_report_with_reason(): void
    {
        $this->boot('any');
        $service = app(ReportApprovalService::class);

        $r = $service->decline($this->ctx['prsUser'], $this->ctx['report']->fresh(), 'Numbers off');
        $this->assertSame(ReportStatus::Returned, $r->status);
        $this->assertNull($r->current_stage_id);
        $this->assertDatabaseHas('indicator_report_approvals', ['report_id' => $r->id, 'action' => 'declined', 'reason' => 'Numbers off']);
    }
}
