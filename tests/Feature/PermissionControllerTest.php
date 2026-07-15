<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_permission(): void
    {
        $admin = User::create(['full_name' => 'Ad', 'email' => 'ad@x.io', 'password' => bcrypt('x'), 'is_admin' => true]);

        $this->actingAs($admin)->post('/settings/permissions', [
            'permission' => 'manage-widgets', 'label' => 'Manage Widgets',
        ])->assertRedirect();

        $this->assertTrue(Permission::where('permission', 'manage-widgets')->exists());
    }
}
