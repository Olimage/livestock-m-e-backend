<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([

            ZonesSeeder::class,
            RoleSeeder::class,
            ModuleSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,

            // RBAC: catalog -> role grants -> user roles (needs roles/modules/users above)
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserRoleSeeder::class,

            MockDataSeeder::class,
            StrategicAlignmentSeeder::class,
            ResultChainIndicatorSeeder::class,
            DisagregationSeeder::class,
            ProgramSeeder::class,
            ProgramDetailsSeeder::class,
            CrossCuttingMetricSeeder::class,
            BondDeliverableSeeder::class,

            // Indicator reporting & approval workflow (needs roles + departments above)
            ReportingPeriodSeeder::class,
            IndicatorReportingSettingsSeeder::class,
            ApprovalWorkflowSeeder::class,
            DashboardSampleSeeder::class,
            ReportingWorkflowStatesSeeder::class,
            // Catalog MUST precede role assignments (assignments look up permission ids).
            DashboardPermissionsSeeder::class,
            DashboardRoleAssignmentsSeeder::class,
        ]);
    }
}
