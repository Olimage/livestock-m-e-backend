<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionResolverTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attrs = []): User
    {
        return User::create(array_merge([
            'full_name' => 'U', 'email' => uniqid().'@x.io', 'password' => bcrypt('x'),
        ], $attrs));
    }

    public function test_admin_has_everything(): void
    {
        $this->assertTrue($this->user(['is_admin' => true])->hasPermission('anything'));
    }

    public function test_role_grants_permission(): void
    {
        $perm = Permission::create(['permission' => 'manage-programs']);
        $role = Role::create(['name' => 'Dir', 'slug' => 'dir']);
        $role->permissions()->sync([$perm->id]);
        $user = $this->user();
        $user->roles()->sync([$role->id]);

        $this->assertTrue($user->hasPermission('manage-programs'));
        $this->assertFalse($user->hasPermission('manage-users'));
    }

    public function test_direct_grant_permission(): void
    {
        $perm = Permission::create(['permission' => 'manage-users']);
        $user = $this->user();
        $user->permissions()->sync([$perm->id]);

        $this->assertTrue($user->hasPermission('manage-users'));
    }
}
