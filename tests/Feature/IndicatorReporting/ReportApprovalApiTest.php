<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\OutputIndicator;
use App\Models\Permission;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Services\IndicatorReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

class ReportApprovalApiTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    /** Grant an approval permission to a role (mirrors RolePermissionSeeder). */
    private function grant(Role $role, string $permission): void
    {
        $perm = Permission::firstOrCreate(['permission' => $permission], ['label' => $permission]);
        $role->permissions()->syncWithoutDetaching([$perm->id]);
    }

    public function test_prs_approves_and_ps_publishes_via_api(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $ps = Role::create(['name' => 'PS', 'slug' => 'permanent_secretary']);
        $this->grant($prs, 'review-indicator-reports');
        $this->grant($ps, 'approve-indicator-reports');
        $wf = ApprovalWorkflow::create(['name' => 'WF', 'slug' => 'wf', 'is_active' => true, 'resubmit_behavior' => 'from_start']);
        $wf->stages()->create(['name' => 'Validate', 'position' => 1, 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any']);
        $wf->stages()->create(['name' => 'Final', 'position' => 2, 'assignment_type' => 'role', 'role_id' => $ps->id, 'approval_mode' => 'any']);
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

        $this->withHeaders($this->authHeaders($prsUser))
            ->postJson("/api/v1/indicator-reports/{$report->uuid}/approve")
            ->assertOk()->assertJsonPath('data.status', 'pending');

        $this->withHeaders($this->authHeaders($psUser))
            ->postJson("/api/v1/indicator-reports/{$report->uuid}/approve")
            ->assertOk()->assertJsonPath('data.status', 'approved');
    }

    public function test_decline_requires_reason(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $this->grant($prs, 'review-indicator-reports');
        $wf = ApprovalWorkflow::create(['name' => 'WF', 'slug' => 'wf', 'is_active' => true, 'resubmit_behavior' => 'from_start']);
        $wf->stages()->create(['name' => 'Validate', 'position' => 1, 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any']);
        $wf->departments()->attach($dept->id);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = User::create(['full_name' => 'Dir', 'email' => 'dir@x.io', 'password' => 'secret123']);
        $director->departments()->attach($dept->id);
        $prsUser = User::create(['full_name' => 'PRS', 'email' => 'prs@x.io', 'password' => 'secret123']);
        $prsUser->roles()->attach($prs->id);

        $reports = app(IndicatorReportService::class);
        $report = $reports->submit($director, $reports->create($director, [
            'indicator_type' => OutputIndicator::class, 'indicator_id' => $indicator->id,
            'department_id' => $dept->id, 'reporting_period_id' => $period->id, 'actual_value' => 80,
        ]));

        $this->withHeaders($this->authHeaders($prsUser))
            ->postJson("/api/v1/indicator-reports/{$report->uuid}/decline", [])
            ->assertStatus(422);

        $this->withHeaders($this->authHeaders($prsUser))
            ->postJson("/api/v1/indicator-reports/{$report->uuid}/decline", ['reason' => 'Wrong'])
            ->assertOk()->assertJsonPath('data.status', 'returned');
    }
}
