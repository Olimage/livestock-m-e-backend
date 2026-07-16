<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

class WorkflowApiTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    public function test_admin_can_create_workflow_via_api(): void
    {
        $admin = User::create(['full_name' => 'Admin', 'email' => 'a@x.io', 'password' => 'secret123', 'is_admin' => true]);
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);

        $response = $this->withHeaders($this->authHeaders($admin))->postJson('/api/v1/workflows', [
            'name' => 'Two Step',
            'resubmit_behavior' => 'from_start',
            'stages' => [['name' => 'Validate', 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any']],
            'department_ids' => [$dept->id],
        ]);

        $response->assertCreated()->assertJsonPath('success', true);
        $this->assertDatabaseHas('approval_workflows', ['name' => 'Two Step']);
    }

    public function test_non_admin_without_permission_is_forbidden(): void
    {
        $user = User::create(['full_name' => 'U', 'email' => 'u@x.io', 'password' => 'secret123']);

        $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/workflows', ['name' => 'X', 'stages' => [], 'department_ids' => []])
            ->assertForbidden();
    }
}
