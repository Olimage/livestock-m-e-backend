<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Animal Traceability',
                'slug' => 'animal-traceability',
                'description' => 'Module for tracking and tracing animals'
            ],
            [
                'name' => 'Health Management',
                'slug' => 'health-management',
                'description' => 'Module for managing health records and services'
            ],
            [
                'name' => 'Markets & Prices',
                'slug' => 'markets-prices',
                'description' => 'Module for monitoring market trends and prices'
            ],
            [
                'name' => 'Infrastructure',
                'slug' => 'infrastructure',
                'description' => 'Module for managing infrastructure projects and assets'
            ],
            [
                'name' => 'Actors',
                'slug' => 'actors',
                'description' => 'Module for managing stakeholders and participants'
            ],
            [
                'name' => 'PLANNING, ACTIVITIES & OPERATIONAL REPORTING (PAR)',
                'slug' => 'planning-activities-operational-reporting-par',
                'description' => 'Module for planning, activities, and operational reporting'
            ],
            [
                'name' => 'Cross-cutting entities',
                'slug' => 'cross-cutting-entities',
                'description' => 'Module for managing cross-cutting entities'
            ],
            [
                'name' => 'Integration Entities',
                'slug' => 'integration-entities',
                'description' => 'Module for managing integration entities'
            ],
            [
                'name' => 'Indicator Registry',
                'slug' => 'indicator-registry',
                'description' => 'Module for managing indicator registry'
            ],
            [
                'name' => 'Reserved Domains',
                'slug' => 'reserved-domains',
                'description' => 'Module for managing reserved domains'
            ]
        ];

        foreach ($data as $datum) {
            Module::create($datum);
        }
    }
}
