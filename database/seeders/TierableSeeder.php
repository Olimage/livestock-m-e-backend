<?php

namespace Database\Seeders;

use App\Models\BondOutcome;
use App\Models\Indicator;
use App\Models\NlgasPillar;
use App\Models\PresidentialPriority;
use App\Models\SectoralGoal;
use App\Models\Tier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TierableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tiers
        $tier0 = Tier::where('tier', 'Tier 0')->first();
        $tier1 = Tier::where('tier', 'Tier 1')->first();
        $tier2 = Tier::where('tier', 'Tier 2')->first();
        $tier3 = Tier::where('tier', 'Tier 3')->first();

        if (!$tier0 || !$tier1 || !$tier2 || !$tier3) {
            $this->command->warn('Tiers not found. Please run TierSeeder first.');
            return;
        }

        $presidentialPriorities = PresidentialPriority::all();
        foreach ($presidentialPriorities as $pp) {
            $pp->tiers()->attach($tier0->id);
        }
        $this->command->info('Attached Tier 0 to all Presidential Priorities');

        $sectoralGoals = SectoralGoal::all();
        foreach ($sectoralGoals as $sg) {
            $sg->tiers()->attach($tier1->id);
        }
        $this->command->info('Attached Tier 1 to all Sectoral Goals');

        $bondOutcomes = BondOutcome::all();
        foreach ($bondOutcomes as $bo) {
            $bo->tiers()->sync([$tier1->id, $tier2->id]);
        }
        $this->command->info('Attached Tier 1 and Tier 2 to all Bond Outcomes');

        $pillars = NlgasPillar::all();
        foreach ($pillars as $pillar) {
            $pillar->tiers()->attach($tier1->id);
        }
        $this->command->info('Attached Tier 1 to all NLGAs Pillars');

        $impactIndicators = Indicator::where('indicator_type', 'impact')->get();
        foreach ($impactIndicators as $indicator) {
            $indicator->tiers()->attach($tier0->id);
        }
        $this->command->info('Attached Tier 0 to all Impact Indicators');

        $outcomeIndicators = Indicator::where('indicator_type', 'outcome')->get();
        foreach ($outcomeIndicators as $indicator) {
            $indicator->tiers()->attach($tier1->id);
        }
        $this->command->info('Attached Tier 1 to all Outcome Indicators');
    }
}
