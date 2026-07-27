<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\Department;
use App\Models\User;
use App\Support\DepartmentHierarchy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

/**
 * Users are attached to every department on their branch. The unit they belong
 * to — and scope reporting by — is the deepest one, not the root.
 */
class DepartmentBranchTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    private function branchUser(): array
    {
        $ministry = Department::create(['name' => 'Ministry', 'slug' => 'ministry']);
        $technical = Department::create(['name' => 'Technical Departments', 'slug' => 'technical', 'parent_id' => $ministry->id]);
        $animalHealth = Department::create(['name' => 'Animal Health', 'slug' => 'animal-health', 'parent_id' => $technical->id]);

        $user = User::create(['full_name' => 'Dir', 'email' => 'dir@x.io', 'password' => 'secret123']);
        // Attached out of order on purpose — resolution must not depend on it.
        $user->departments()->attach([$animalHealth->id, $ministry->id, $technical->id]);

        return [$user, $ministry, $technical, $animalHealth];
    }

    public function test_branch_is_ordered_root_first_with_depth(): void
    {
        [$user, $ministry, $technical, $animalHealth] = $this->branchUser();

        $branch = $user->departmentBranch();

        $this->assertSame(
            [$ministry->id, $technical->id, $animalHealth->id],
            $branch->pluck('id')->all()
        );
        $this->assertSame([0, 1, 2], $branch->pluck('depth')->all());
    }

    public function test_primary_department_is_the_deepest_unit(): void
    {
        [$user, , , $animalHealth] = $this->branchUser();

        $this->assertSame($animalHealth->id, $user->primaryDepartment()->id);
        $this->assertSame('Animal Health', $user->primaryDepartment()->name);
    }

    public function test_auth_me_exposes_the_branch_and_the_primary_unit(): void
    {
        [$user, $ministry, , $animalHealth] = $this->branchUser();

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.departments.0.id', $ministry->id)
            ->assertJsonPath('data.user.departments.2.id', $animalHealth->id)
            ->assertJsonPath('data.user.primary_department.id', $animalHealth->id)
            ->assertJsonPath('data.user.primary_department.name', 'Animal Health');
    }

    public function test_user_profile_matches_the_same_shape(): void
    {
        [$user, , , $animalHealth] = $this->branchUser();

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/user/profile')
            ->assertOk()
            ->assertJsonPath('data.primary_department.id', $animalHealth->id)
            ->assertJsonPath('data.departments.2.id', $animalHealth->id);
    }

    public function test_a_user_on_a_single_department_is_unaffected(): void
    {
        $dept = Department::create(['name' => 'Solo', 'slug' => 'solo']);
        $user = User::create(['full_name' => 'Solo', 'email' => 'solo@x.io', 'password' => 'secret123']);
        $user->departments()->attach($dept->id);

        $this->assertSame($dept->id, $user->primaryDepartment()->id);
        $this->assertSame(0, $user->departmentBranch()->first()->depth);
    }

    public function test_a_user_with_no_department_has_no_primary(): void
    {
        $user = User::create(['full_name' => 'None', 'email' => 'none@x.io', 'password' => 'secret123']);

        $this->assertNull($user->primaryDepartment());
        $this->assertTrue($user->departmentBranch()->isEmpty());
    }

    public function test_depth_survives_a_cycle_in_parent_ids(): void
    {
        $a = Department::create(['name' => 'A', 'slug' => 'a']);
        $b = Department::create(['name' => 'B', 'slug' => 'b', 'parent_id' => $a->id]);
        $a->update(['parent_id' => $b->id]);

        $depth = DepartmentHierarchy::depth($a->id, DepartmentHierarchy::parentMap());

        $this->assertIsInt($depth);
    }
}
