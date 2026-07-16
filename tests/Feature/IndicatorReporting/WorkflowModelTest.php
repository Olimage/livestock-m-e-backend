<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_orders_stages_and_links_departments(): void
    {
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $ps = Role::create(['name' => 'Permanent Secretary', 'slug' => 'permanent_secretary']);
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);

        $wf = ApprovalWorkflow::create([
            'name' => 'Standard', 'slug' => 'standard', 'is_active' => true,
            'resubmit_behavior' => 'from_start',
        ]);
        $wf->stages()->create(['name' => 'Final', 'position' => 2, 'assignment_type' => 'role', 'role_id' => $ps->id, 'approval_mode' => 'any']);
        $wf->stages()->create(['name' => 'Validate', 'position' => 1, 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any']);
        $wf->departments()->attach($dept->id);

        $this->assertSame(['Validate', 'Final'], $wf->fresh()->stages->pluck('name')->all());
        $this->assertTrue($wf->departments->contains($dept->id));
    }
}
