<?php

namespace Tests\Feature;

use App\Models\Role;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_and_role_grants_seed(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(ModuleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $admin = Role::where('slug', 'admin')->first();
        $this->assertTrue($admin->permissions()->where('permission', 'manage-programs')->exists());
        $this->assertTrue($admin->permissions()->where('permission', 'manage-users')->exists());
    }
}
