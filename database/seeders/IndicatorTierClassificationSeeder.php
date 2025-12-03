<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tier;

class IndicatorTierClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tier::firstOrCreate([
            'tier' => 'Tier 0',
            'level' => 'Impact',
            'measurement_frequency' => 'Annual,Multi-year',
            'attribution' => 'Contribution (many external factors)'
        ]);
        
        Tier::firstOrCreate([
            'tier' => 'Tier 1',
            'level' => 'Impact',
            'measurement_frequency' => 'Quarterly, Annual',
            'attribution' => 'Contribution (many external factors)'
        ]);

        Tier::firstOrCreate([
            'tier' => 'Tier 2',
            'level' => 'Outcome',
            'measurement_frequency' => 'Quarterly, Annual',
            'attribution' => 'Strong contribution to moderate attribution'
        ]);

        Tier::firstOrCreate([
            'tier' => 'Tier 3',
            'level' => 'Output',
            'measurement_frequency' => 'Weekly, Monthly, Quarterly',
            'attribution' => 'Direct attribution'
        ]);
    }
}
