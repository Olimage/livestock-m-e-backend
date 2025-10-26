<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

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
        ];

        foreach($data as $datum){
            Role::create($datum);
        }
    }
}
