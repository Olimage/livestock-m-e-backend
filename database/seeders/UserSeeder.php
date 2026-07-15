<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolesBySlug = Role::pluck('id', 'slug');

        $superAdmin = User::create([
            'full_name' => 'Super Admin',
            'email' => 'superadmin@fmld.gov',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $admin = User::create([
            'full_name' => 'Admin User',
            'email' => 'admin@fmld.gov',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        if (isset($rolesBySlug['super_admin'])) {
            $superAdmin->roles()->syncWithoutDetaching([$rolesBySlug['super_admin']]);
        }
        if (isset($rolesBySlug['admin'])) {
            $admin->roles()->syncWithoutDetaching([$rolesBySlug['admin']]);
        }
    }
}
