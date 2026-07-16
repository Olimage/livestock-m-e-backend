<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\OutputIndicator;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Services\IndicatorReportService;
use App\Services\ReportApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

class ApprovalTrailTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    public function test_trail_lists_actions_in_order_and_admin_sees_all(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $wf = ApprovalWorkflow::create(['name' => 'WF', 'slug' => 'wf', 'is_active' => true, 'resubmit_behavior' => 'from_start']);
        $wf->stages()->create(['name' => 'Validate', 'position' => 1, 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any']);
        $wf->departments()->attach($dept->id);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);

        $director = User::create(['full_name' => 'Dir', 'email' => 'dir@x.io', 'password' => 'secret123']);
        $director->departments()->attach($dept->id);
        $prsUser = User::create(['full_name' => 'PRS', 'email' => 'prs@x.io', 'password' => 'secret123']);
        $prsUser->roles()->attach($prs->id);
        $admin = User::create(['full_name' => 'Admin', 'email' => 'admin@x.io', 'password' => 'secret123', 'is_admin' => true]);

        $reports = app(IndicatorReportService::class);
        $report = $reports->submit($director, $reports->create($director, [
            'indicator_type' => OutputIndicator::class, 'indicator_id' => $indicator->id,
            'department_id' => $dept->id, 'reporting_period_id' => $period->id, 'actual_value' => 80,
        ]));
        app(ReportApprovalService::class)->approve($prsUser, $report->fresh());

        $this->withHeaders($this->authHeaders($admin))
            ->getJson("/api/v1/indicator-reports/{$report->uuid}/trail")
            ->assertOk()
            ->assertJsonPath('data.0.action', 'submitted');

        $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/v1/approval-trails')
            ->assertOk()->assertJsonPath('success', true);
    }
}
