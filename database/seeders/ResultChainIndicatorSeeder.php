<?php

namespace Database\Seeders;

use App\Models\ImpactIndicator;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use Illuminate\Database\Seeder;

class ResultChainIndicatorSeeder extends Seeder
{
    /**
     * Seed a minimal set of Result Chain indicators so pickers
     * (Baselines, Bond Deliverables) are not empty on a fresh install.
     */
    public function run(): void
    {
        if (OutputIndicator::count() === 0) {
            OutputIndicator::create(['title' => 'Sample output indicator', 'measurement_unit' => 'count']);
        }

        if (OutcomeIndicator::count() === 0) {
            OutcomeIndicator::create(['title' => 'Sample outcome indicator', 'measurement_unit' => '%']);
        }

        if (ImpactIndicator::count() === 0) {
            ImpactIndicator::create(['title' => 'Sample impact indicator', 'measurement_unit' => '%']);
        }
    }
}
