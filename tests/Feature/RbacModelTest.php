<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_and_user_pivots_link(): void
    {
        $perm = Permission::create(['permission' => 'manage-programs', 'label' => 'Manage Programs']);
        $role = Role::create(['name' => 'Director', 'slug' => 'director']);
        $role->permissions()->sync([$perm->id]);

        $user = User::create(['full_name' => 'A', 'email' => 'a@x.io', 'password' => bcrypt('x')]);
        $user->roles()->sync([$role->id]);

        $this->assertTrue($role->permissions()->where('permission', 'manage-programs')->exists());
        $this->assertTrue($user->roles()->where('slug', 'director')->exists());
    }
}
