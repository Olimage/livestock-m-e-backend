<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IndicatorTierClassification;

class IndicatorTierClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IndicatorTierClassification::firstOrCreate([
            'tier' => 'Tier 1',
            'level' => 'Impact',
            'measurement_frequency' => 'Annual/Multi-year',
            'attribution' => 'Contribution (many external factors)'
        ]);

        IndicatorTierClassification::firstOrCreate([
            'tier' => 'Tier 2',
            'level' => 'Outcome',
            'measurement_frequency' => 'Quaterly/Annual',
            'attribution' => 'Strong contribution to moderate attribution'
        ]);

        IndicatorTierClassification::firstOrCreate([
            'tier' => 'Tier 3',
            'level' => 'Output',
            'measurement_frequency' => 'Weekly / Monthly / Quarterly',
            'attribution' => 'Direct attribution'
        ]);
    }
}
