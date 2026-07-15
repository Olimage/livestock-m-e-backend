<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_roles_and_departments_to_user(): void
    {
        $admin = User::create(['full_name' => 'Ad', 'email' => 'ad@x.io', 'password' => bcrypt('x'), 'is_admin' => true]);
        $target = User::create(['full_name' => 'T', 'email' => 't@x.io', 'password' => bcrypt('x')]);
        $role = Role::create(['name' => 'Director', 'slug' => 'director']);
        $dept = Department::create(['name' => 'Vet', 'slug' => 'vet']);

        $this->actingAs($admin)->put("/settings/users/{$target->id}", [
            'full_name' => 'T', 'email' => 't@x.io',
            'role_ids' => [$role->id], 'department_ids' => [$dept->id], 'permission_ids' => [],
        ])->assertRedirect();

        $target->refresh();
        $this->assertTrue($target->roles()->where('slug', 'director')->exists());
        $this->assertTrue($target->departments()->where('departments.id', $dept->id)->exists());
    }

    public function test_non_manager_cannot_update_users(): void
    {
        $plain = User::create(['full_name' => 'P', 'email' => 'p@x.io', 'password' => bcrypt('x')]);
        $target = User::create(['full_name' => 'T', 'email' => 't@x.io', 'password' => bcrypt('x')]);

        $this->actingAs($plain)->put("/settings/users/{$target->id}", [
            'full_name' => 'Hacked', 'email' => 't@x.io', 'is_admin' => true,
        ])->assertForbidden();

        $this->assertFalse($target->fresh()->is_admin);
    }

    public function test_manage_users_delegate_cannot_grant_admin_or_roles(): void
    {
        // Delegate has manage-users (via a role) but is NOT a full admin.
        $perm = \App\Models\Permission::create(['permission' => 'manage-users']);
        $mgrRole = Role::create(['name' => 'User Manager', 'slug' => 'user_manager']);
        $mgrRole->permissions()->sync([$perm->id]);
        $delegate = User::create(['full_name' => 'D', 'email' => 'd@x.io', 'password' => bcrypt('x')]);
        $delegate->roles()->sync([$mgrRole->id]);

        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $target = User::create(['full_name' => 'T', 'email' => 't@x.io', 'password' => bcrypt('x')]);

        $this->actingAs($delegate)->put("/settings/users/{$target->id}", [
            'full_name' => 'T', 'email' => 't@x.io',
            'is_admin' => true, 'role_ids' => [$adminRole->id],
        ])->assertRedirect();

        $target->refresh();
        $this->assertFalse($target->is_admin, 'Delegate must not be able to grant admin.');
        $this->assertFalse($target->roles()->where('slug', 'admin')->exists(), 'Delegate must not assign roles.');
    }

    /** A manage-users delegate (with its own permission) for reuse in guardrail tests. */
    private function delegate(): User
    {
        $perm = \App\Models\Permission::firstOrCreate(['permission' => 'manage-users']);
        $role = Role::firstOrCreate(['slug' => 'user_manager'], ['name' => 'User Manager']);
        $role->permissions()->syncWithoutDetaching([$perm->id]);
        $u = User::create(['full_name' => 'D', 'email' => uniqid().'@x.io', 'password' => bcrypt('x')]);
        $u->roles()->sync([$role->id]);

        return $u;
    }

    public function test_delegate_cannot_modify_a_privileged_user(): void
    {
        $admin = User::create(['full_name' => 'A', 'email' => 'a@x.io', 'password' => bcrypt('x'), 'is_admin' => true]);

        $this->actingAs($this->delegate())->put("/settings/users/{$admin->id}", [
            'full_name' => 'Hijacked', 'email' => 'a@x.io',
        ])->assertForbidden();

        $this->assertEquals('A', $admin->fresh()->full_name);
    }

    public function test_delegate_cannot_change_email(): void
    {
        $target = User::create(['full_name' => 'T', 'email' => 'orig@x.io', 'password' => bcrypt('x')]);

        $this->actingAs($this->delegate())->put("/settings/users/{$target->id}", [
            'full_name' => 'T', 'email' => 'attacker@x.io',
        ])->assertForbidden();

        $this->assertEquals('orig@x.io', $target->fresh()->email);
    }
}
