<?php

namespace Database\Seeders;

use App\Models\ReportingPeriod;
use Illuminate\Database\Seeder;

class ReportingPeriodSeeder extends Seeder
{
    public function run(): void
    {
        // Cover previous, current and next year so reporting can be done for
        // prior periods. Past years are closed; current + next stay open.
        $currentYear = (int) date('Y');
        foreach (range($currentYear - 2, $currentYear + 1) as $year) {
            foreach ([1, 2, 3, 4] as $q) {
                ReportingPeriod::updateOrCreate(
                    ['type' => 'quarter', 'year' => $year, 'period_number' => $q],
                    ['name' => "Q{$q} {$year}", 'is_open' => $year >= $currentYear],
                );
            }
        }
    }
}
