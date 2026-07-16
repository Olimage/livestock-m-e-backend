<?php

namespace Database\Seeders;

use App\Models\ReportingPeriod;
use Illuminate\Database\Seeder;

class ReportingPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) date('Y');
        foreach ([1, 2, 3, 4] as $q) {
            ReportingPeriod::updateOrCreate(
                ['type' => 'quarter', 'year' => $year, 'period_number' => $q],
                ['name' => "Q{$q} {$year}", 'is_open' => true],
            );
        }
    }
}
