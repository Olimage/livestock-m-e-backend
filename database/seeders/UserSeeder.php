<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'full_name' => 'Super Admin',
            'email' => 'superadmin@fmld.gov',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_admin' => true,
        ]);
        
        User::create([
            'full_name' => 'Admin User',
            'email' => 'admin@fmld.gov',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_admin' => true,
        ]);       
        
    }
}
