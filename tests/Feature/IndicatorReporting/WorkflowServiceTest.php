<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\Department;
use App\Models\Role;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_workflow_with_ordered_stages_and_department(): void
    {
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);

        $wf = app(WorkflowService::class)->create([
            'name' => 'Two Step',
            'resubmit_behavior' => 'from_start',
            'stages' => [
                ['name' => 'Validate', 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any'],
            ],
            'department_ids' => [$dept->id],
        ]);

        $this->assertSame(1, $wf->stages()->count());
        $this->assertSame(1, $wf->stages()->first()->position);
        $this->assertTrue($wf->departments->contains($dept->id));
    }

    public function test_department_cannot_have_two_active_workflows(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $service = app(WorkflowService::class);
        $service->create(['name' => 'A', 'is_active' => true, 'stages' => [], 'department_ids' => [$dept->id]]);
        $b = $service->create(['name' => 'B', 'is_active' => true, 'stages' => [], 'department_ids' => []]);

        $this->expectException(\DomainException::class);
        $service->assignDepartments($b, [$dept->id]);
    }
}
