<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /** key => [label, module-slug|null] */
    public const CATALOG = [
        'manage-programs' => ['Manage Programs', null],
        'manage-indicators' => ['Manage Indicators', 'indicator-registry'],
        'manage-departments' => ['Manage Departments', null],
        'manage-bond-deliverables' => ['Manage Bond Deliverables', null],
        'manage-disaggregations' => ['Manage Disaggregations', null],
        'manage-baselines' => ['Manage Baselines', null],
        'manage-cross-cutting-metrics' => ['Manage Cross-Cutting Metrics', null],
        'manage-users' => ['Manage Users', null],
        'manage-permissions' => ['Manage Permissions', null],
        'manage-settings' => ['Manage Settings', null],
        'manage-enumerations' => ['Manage Enumerations', null],
        'view-summary-reports' => ['View Summary Reports', null],
        'view-detailed-reports' => ['View Detailed Reports', null],
        'report-indicator-data' => ['Report Indicator Data', 'indicator-registry'],
        'review-indicator-reports' => ['Review Indicator Reports', 'indicator-registry'],
        'approve-indicator-reports' => ['Approve Indicator Reports', 'indicator-registry'],
        'view-all-indicator-reports' => ['View All Indicator Reports', 'indicator-registry'],
        'manage-approval-workflows' => ['Manage Approval Workflows', null],
        'manage-reporting-periods' => ['Manage Reporting Periods', null],
    ];

    public function run(): void
    {
        foreach (self::CATALOG as $key => [$label, $moduleSlug]) {
            $moduleId = $moduleSlug
                ? Module::where('slug', $moduleSlug)->value('id')
                : null;

            Permission::updateOrCreate(
                ['permission' => $key],
                ['label' => $label, 'module_id' => $moduleId],
            );
        }

        $this->command?->info('Permissions catalog seeded: '.count(self::CATALOG).' keys.');
    }
}
