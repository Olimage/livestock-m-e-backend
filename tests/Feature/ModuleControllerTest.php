<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_module_with_auto_slug(): void
    {
        $admin = User::create(['full_name' => 'Ad', 'email' => 'ad@x.io', 'password' => bcrypt('x'), 'is_admin' => true]);

        $this->actingAs($admin)->post('/settings/modules', [
            'name' => 'Animal Health', 'description' => 'x',
        ])->assertRedirect();

        $module = Module::where('name', 'Animal Health')->first();
        $this->assertNotNull($module);
        $this->assertEquals('animal-health', $module->slug);
    }
}
