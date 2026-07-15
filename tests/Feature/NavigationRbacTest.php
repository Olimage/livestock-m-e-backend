<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NavigationRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_with_manage_programs_sees_system_menu(): void
    {
        $perm = Permission::create(['permission' => 'manage-programs']);
        $role = Role::create(['name' => 'Dir', 'slug' => 'dir']);
        $role->permissions()->sync([$perm->id]);
        $user = User::create(['full_name' => 'U', 'email' => 'u@x.io', 'password' => bcrypt('x')]);
        $user->roles()->sync([$role->id]);

        Auth::login($user);
        $nav = collect(NavigationService::getNavigation())->pluck('name');
        Auth::logout();

        $this->assertTrue($nav->contains('System'));
    }
}
