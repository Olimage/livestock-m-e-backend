<?php

namespace Database\Seeders;

use App\Models\NlgasPillar;
use App\Models\PresidentialPriority;
use App\Models\SectoralGoal;
use App\Models\Tier;
use Illuminate\Database\Seeder;

class TierableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tier0 = Tier::where('tier', 'Tier 0')->first();
        $tier1 = Tier::where('tier', 'Tier 1')->first();

        if (!$tier0 || !$tier1) {
            $this->command->warn('Tiers not found. Please run IndicatorTierClassificationSeeder first.');
            return;
        }

        $presidentialPriorities = PresidentialPriority::all();
        foreach ($presidentialPriorities as $pp) {
            $pp->tiers()->syncWithoutDetaching([$tier0->id]);
        }
        $this->command->info('Attached Tier 0 to all Presidential Priorities');

        $sectoralGoals = SectoralGoal::all();
        foreach ($sectoralGoals as $sg) {
            $sg->tiers()->syncWithoutDetaching([$tier1->id]);
        }
        $this->command->info('Attached Tier 1 to all Sectoral Goals');

        $pillars = NlgasPillar::all();
        foreach ($pillars as $pillar) {
            $pillar->tiers()->syncWithoutDetaching([$tier1->id]);
        }
        $this->command->info('Attached Tier 1 to all NLGAS Pillars');
    }
}
