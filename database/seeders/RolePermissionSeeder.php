<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /** role-slug => [permission keys] ; ['*'] means all. */
    public const GRANTS = [
        'super_admin' => ['*'],
        'admin' => ['*'],
        'director' => [
            'manage-programs', 'manage-indicators', 'manage-departments',
            'manage-bond-deliverables', 'manage-baselines',
            'view-summary-reports', 'view-detailed-reports',
        ],
        'hod' => ['manage-indicators', 'manage-baselines', 'view-summary-reports'],
        'supervisor' => ['manage-enumerations'],
        'enumerator' => ['manage-enumerations'],
    ];

    public function run(): void
    {
        $allIds = Permission::pluck('id', 'permission');

        foreach (self::GRANTS as $slug => $keys) {
            $role = Role::where('slug', $slug)->first();
            if (! $role) {
                continue;
            }

            $ids = $keys === ['*']
                ? $allIds->values()->all()
                : collect($keys)->map(fn ($k) => $allIds[$k] ?? null)->filter()->values()->all();

            $role->permissions()->syncWithoutDetaching($ids);
        }
    }
}
