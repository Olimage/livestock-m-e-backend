<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ApprovalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $prs = Role::where('slug', 'prs')->first();
        $ps = Role::where('slug', 'permanent_secretary')->first();
        $director = Role::where('slug', 'director')->first();

        $wf = ApprovalWorkflow::updateOrCreate(
            ['slug' => 'standard-me-approval'],
            [
                'name' => 'Standard M&E Approval',
                'description' => 'Director reports, PRS validates, Permanent Secretary approves.',
                'is_active' => true,
                'initiator_role_id' => $director?->id,
                'resubmit_behavior' => 'from_start',
            ],
        );

        $wf->stages()->delete();
        $wf->stages()->create([
            'name' => 'PRS Validation', 'position' => 1,
            'assignment_type' => 'role', 'role_id' => $prs?->id, 'approval_mode' => 'any', 'is_active' => true,
        ]);
        $wf->stages()->create([
            'name' => 'Permanent Secretary Approval', 'position' => 2,
            'assignment_type' => 'role', 'role_id' => $ps?->id, 'approval_mode' => 'any', 'is_active' => true,
        ]);

        $wf->departments()->sync(Department::pluck('id')->all());
    }
}
