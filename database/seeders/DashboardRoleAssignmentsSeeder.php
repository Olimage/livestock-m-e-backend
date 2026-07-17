<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Assigns the dashboard permission catalog to roles and creates non-admin role
 * test users so backend-driven access can be exercised. Idempotent.
 *
 * Password for test users is "password" (User casts password => hashed, so the
 * plain value is passed and hashed by the cast — do NOT Hash::make here).
 *
 * Run: php artisan db:seed --class=DashboardRoleAssignmentsSeeder
 */
class DashboardRoleAssignmentsSeeder extends Seeder
{
    public function run(): void
    {
        $views = [
            'view-executive-dashboard', 'view-sector-outcomes', 'view-bond-performance',
            'view-strategic-programs', 'view-data-health', 'view-results-chain', 'view-sustainability',
        ];

        $map = [
            'permanent_secretary' => [...$views, 'generate-report'],
            'minister' => [...$views, 'generate-report', 'edit-report', 'approve-report'],
            'prs' => [...$views, 'generate-report', 'edit-report', 'approve-report'],
            'director' => [...$views, 'create-report'],
            'hod' => ['view-executive-dashboard', 'view-data-health'],
        ];

        foreach ($map as $slug => $keys) {
            $role = Role::where('slug', $slug)->first();
            if (! $role) {
                continue;
            }
            $ids = Permission::whereIn('permission', $keys)->pluck('id');
            $role->permissions()->syncWithoutDetaching($ids);
        }

        // Non-admin role test users (password: "password" — hashed by the model cast).
        $testUsers = [
            ['email' => 'ps@fmld.gov', 'name' => 'PS Test', 'slug' => 'permanent_secretary'],
            ['email' => 'prs@fmld.gov', 'name' => 'PRS Test', 'slug' => 'prs'],
            ['email' => 'director@fmld.gov', 'name' => 'Director Test', 'slug' => 'director'],
        ];

        foreach ($testUsers as $t) {
            $user = User::updateOrCreate(
                ['email' => $t['email']],
                ['full_name' => $t['name'], 'password' => 'password', 'is_admin' => false]
            );
            $role = Role::where('slug', $t['slug'])->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        $this->command->info('Assigned dashboard permissions to roles + created role test users.');
    }
}
