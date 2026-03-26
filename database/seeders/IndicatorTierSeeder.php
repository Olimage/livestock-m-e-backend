<?php

namespace Database\Seeders;

use App\Models\IndicatorTier;
use Illuminate\Database\Seeder;

class IndicatorTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            ['name' => 'Output',  'prefix' => 'OUT'],
            ['name' => 'Outcome', 'prefix' => 'OC'],
            ['name' => 'Impact',  'prefix' => 'IMP'],
            ['name' => 'Bond Output',  'prefix' => 'BOI'],
        ];

        foreach ($tiers as $tier) {
            IndicatorTier::firstOrCreate(['name' => $tier['name']], ['prefix' => $tier['prefix']]);
        }

        $this->command->info('Indicator tiers seeded successfully.');
    }
}
