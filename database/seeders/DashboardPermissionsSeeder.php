<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Seeds the dashboard permission catalog (page + action level) so the backend
 * can authorize FMLD dashboard access. Idempotent.
 *
 * Run: php artisan db:seed --class=DashboardPermissionsSeeder
 *
 * @see docs/superpowers/specs/2026-07-16-backend-driven-rbac-design.md
 */
class DashboardPermissionsSeeder extends Seeder
{
    /** permission key => label */
    public const KEYS = [
        'view-executive-dashboard' => 'View Executive Dashboard',
        'view-sector-outcomes' => 'View Sector Outcomes',
        'view-bond-performance' => 'View Bond Performance',
        'view-strategic-programs' => 'View Strategic Programs',
        'view-data-health' => 'View Data Health',
        'view-results-chain' => 'View Results Chain',
        'view-sustainability' => 'View Sustainability & Inclusion',
        'generate-report' => 'Generate Report',
        'create-report' => 'Create Report',
        'edit-report' => 'Edit Report',
        'approve-report' => 'Approve Report',
    ];

    public function run(): void
    {
        $module = Module::firstOrCreate(['name' => 'Dashboards'], ['slug' => 'dashboards']);

        foreach (self::KEYS as $key => $label) {
            Permission::updateOrCreate(
                ['permission' => $key],
                ['label' => $label, 'module_id' => $module->id]
            );
        }

        $this->command->info('Seeded '.count(self::KEYS).' dashboard permissions.');
    }
}
