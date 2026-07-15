<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $rolesBySlug = Role::pluck('id', 'slug');
        $hasRoleColumn = Schema::hasColumn('users', 'role');

        User::query()->each(function (User $user) use ($rolesBySlug, $hasRoleColumn) {
            // Prefer the legacy role string if the column still exists; else default.
            $slug = $hasRoleColumn && $user->getAttribute('role')
                ? $user->getAttribute('role')
                : ($user->is_admin ? 'admin' : 'user');

            $roleId = $rolesBySlug[$slug] ?? $rolesBySlug['user'] ?? null;
            if ($roleId) {
                $user->roles()->syncWithoutDetaching([$roleId]);
            }
        });
    }
}
