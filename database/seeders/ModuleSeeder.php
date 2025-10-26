<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Baseline Dshaboard',
                'slug' => 'baseline_dashboard'
            ],
            [
                'name' => 'Baseline Mobile',
                'slug' => 'baseline_mobile'
            ],
            [
                'name' => 'Monitoring and Evaluation Dashboard',
                'slug' => 'monitoring_evaluation'
            ],
            [
                'name' => 'NNIMS',
                'slug' => 'nnims'
            ],
            [
                'name' => 'Administrations',
                'slug' => 'admin'
            ]
            
        ];

        foreach($data as $datum){

            Module::create($datum);
        }
    }
}
