<?php
namespace Database\Seeders;

use App\Models\Role;
use App\Models\Module;
use App\Models\Department;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles       = Role::all();
        $modules     = Module::all();
        $departments = Department::all(); 

        $permissions = [];

        // Define CRUD actions
        $actions = ['create', 'read', 'update', 'delete'];

        
  // ===== ROLES =====
        foreach ($roles as $role) {
            foreach ($actions as $action) {
                $permissions[] = [
                    'callable_type' => Role::class,
                    'callable_id'   => $role->id,
                    'permission'    => "{$role->slug}.{$action}",
                    'description'   => "Allow {$action} access for role {$role->slug}",
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }


           // ===== MODULES =====
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions[] = [
                    'callable_type' => Module::class,
                    'callable_id'   => $module->id,
                    'permission'    => "{$module->slug}.{$action}",
                    'description'   => "Allow {$action} access for module {$module->slug}",
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }


        
        // ===== DEPARTMENTS (optional) =====
        if ($departments->isNotEmpty()) {
            foreach ($departments as $department) {
                foreach ($actions as $action) {
                    $permissions[] = [
                        'callable_type' => Department::class,
                        'callable_id'   => $department->id,
                        'permission'    => "{$department->slug}.{$action}",
                        'description'   => "Allow {$action} access for department {$department->slug}",
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }
        }


        Permission::insert($permissions);

        $this->command->info('Permissions table seeded successfully!');

       
    }
}
