<?php

namespace Tests\Feature\IndicatorReporting\Web;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkflowBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_lists_and_stores_workflow(): void
    {
        $admin = User::create(['full_name' => 'A', 'email' => 'a@x.io', 'password' => 'secret123', 'is_admin' => true]);
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);

        $this->actingAs($admin)
            ->get(route('indicator-reporting.workflows.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('IndicatorReporting/Workflows/Index'));

        $this->actingAs($admin)->post(route('indicator-reporting.workflows.store'), [
            'name' => 'Two Step',
            'resubmit_behavior' => 'from_start',
            'stages' => [['name' => 'Validate', 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any']],
            'department_ids' => [$dept->id],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('approval_workflows', ['name' => 'Two Step']);
    }
}
