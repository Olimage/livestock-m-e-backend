<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Super Admin',
                'slug' => 'super_admin'
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin'
            ],
            [
                'name' => 'Head of Department',
                'slug' => 'hod'
            ],
            [
                'name' => 'Minister',
                'slug' => 'minister'
            ],
            [
                'name' => 'Permanent Secretary',
                'slug' => 'permanent_secretary'
            ],
            [
                'name' => 'Field Agent',
                'slug' => 'field_agent'
            ],
            [
                'name' => 'State Coordinator',
                'slug' => 'state_coordinator'
            ],
            [
                'name' => 'User',
                'slug' => 'user'
            ], 
            [
                'name' => 'Enumerator',
                'slug' => 'enumerator'
            ], 
            [
                'name' => 'Supervisor',
                'slug' => 'supervisor'
            ],
            [
                'name'=> 'Livestock Extension Officer',
                'slug'=> 'livestock_extension_officer'
            ],
        ];

        foreach ($data as $datum) {
            Role::create($datum);
        }
    }
}
