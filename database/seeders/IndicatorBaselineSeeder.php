<?php

namespace Database\Seeders;

use App\Models\ImpactIndicator;
use App\Models\IndicatorBaselineYear;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use Illuminate\Database\Seeder;

/**
 * Seeds baseline/target years for result-chain indicators so the dashboards can
 * show baseline + baseline-year columns and drive the year filter. Idempotent.
 *
 * Run: php artisan db:seed --class=IndicatorBaselineSeeder
 */
class IndicatorBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [ImpactIndicator::class, OutcomeIndicator::class, OutputIndicator::class];
        $count = 0;

        foreach ($classes as $class) {
            foreach ($class::all() as $indicator) {
                $baseline = rand(20, 55);
                $target = $baseline + rand(20, 45);
                IndicatorBaselineYear::updateOrCreate(
                    ['indicatorable_type' => $class, 'indicatorable_id' => $indicator->id],
                    [
                        'baseline_year' => 2024,
                        'target_year' => 2027,
                        'baseline' => $baseline,
                        'target' => $target,
                        'actual' => $baseline + rand(0, $target - $baseline),
                    ]
                );
                $count++;
            }
        }

        $this->command->info("Seeded baselines for {$count} indicators.");
    }
}
