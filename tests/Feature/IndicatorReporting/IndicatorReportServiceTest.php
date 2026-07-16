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
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicatorReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private function seedWorkflowFor(Department $dept): void
    {
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $wf = ApprovalWorkflow::create(['name' => 'WF', 'slug' => 'wf', 'is_active' => true, 'resubmit_behavior' => 'from_start']);
        $wf->stages()->create(['name' => 'Validate', 'position' => 1, 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any']);
        $wf->departments()->attach($dept->id);
    }

    public function test_main_department_user_can_report_but_other_department_cannot(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $other = Department::create(['name' => 'Finance', 'slug' => 'finance']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);

        $insider = User::create(['full_name' => 'A', 'email' => 'a@x.io', 'password' => 'secret123']);
        $insider->departments()->attach($dept->id);
        $outsider = User::create(['full_name' => 'B', 'email' => 'b@x.io', 'password' => 'secret123']);
        $outsider->departments()->attach($other->id);

        $service = app(IndicatorReportService::class);
        $this->assertTrue($service->canReport($insider, $indicator));
        $this->assertFalse($service->canReport($outsider, $indicator));
    }

    public function test_supporting_department_gated_by_setting(): void
    {
        $main = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $support = Department::create(['name' => 'Vet', 'slug' => 'vet']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $main->id, 'measurement_unit' => 'count']);
        $indicator->supportingDepartments()->attach($support->id);

        $user = User::create(['full_name' => 'S', 'email' => 's@x.io', 'password' => 'secret123']);
        $user->departments()->attach($support->id);

        $service = app(IndicatorReportService::class);
        $this->assertFalse($service->canReport($user, $indicator));

        app(SettingService::class)->set(SettingService::ALLOW_SUPPORTING_DEPT, true);
        $this->assertTrue($service->canReport($user, $indicator));
    }

    public function test_submit_moves_report_to_first_stage_and_records_trail(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $this->seedWorkflowFor($dept);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $user = User::create(['full_name' => 'A', 'email' => 'a@x.io', 'password' => 'secret123']);
        $user->departments()->attach($dept->id);

        $service = app(IndicatorReportService::class);
        $report = $service->create($user, [
            'indicator_type' => OutputIndicator::class, 'indicator_id' => $indicator->id,
            'department_id' => $dept->id, 'reporting_period_id' => $period->id,
            'target_value' => 100, 'actual_value' => 80, 'narrative' => 'ok',
        ]);
        $this->assertSame(ReportStatus::Draft, $report->status);

        $submitted = $service->submit($user, $report->fresh());
        $this->assertSame(ReportStatus::Pending, $submitted->status);
        $this->assertNotNull($submitted->current_stage_id);
        $this->assertDatabaseHas('indicator_report_approvals', ['report_id' => $report->id, 'action' => 'submitted']);
    }
}
