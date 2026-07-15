<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_role_with_permissions(): void
    {
        $admin = User::create(['full_name' => 'Ad', 'email' => 'ad@x.io', 'password' => bcrypt('x'), 'is_admin' => true]);
        $perm = Permission::create(['permission' => 'manage-programs']);

        $this->actingAs($admin)->post('/settings/roles', [
            'name' => 'Director', 'slug' => 'director', 'permission_ids' => [$perm->id],
        ])->assertRedirect();

        $role = Role::where('slug', 'director')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->permissions()->where('permission', 'manage-programs')->exists());
    }
}
