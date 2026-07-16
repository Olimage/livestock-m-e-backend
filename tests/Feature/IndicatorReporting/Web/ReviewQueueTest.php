<?php

namespace Tests\Feature\IndicatorReporting\Web;

use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\OutputIndicator;
use App\Models\Permission;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Services\IndicatorReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReviewQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_prs_sees_pending_report_in_queue_and_can_approve(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $perm = Permission::create(['permission' => 'review-indicator-reports', 'label' => 'Review']);
        $prs->permissions()->attach($perm->id);

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

        $this->actingAs($prsUser)
            ->get(route('indicator-reporting.review.queue'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('IndicatorReporting/Review/Queue')
                ->has('reports', 1));

        $this->actingAs($prsUser)
            ->post(route('indicator-reporting.review.approve', $report->uuid))
            ->assertRedirect()->assertSessionHas('success');
    }
}
