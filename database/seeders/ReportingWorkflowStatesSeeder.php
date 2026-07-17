<?php

namespace Database\Seeders;

use App\Enums\ReportStatus;
use App\Models\IndicatorReport;
use App\Models\ReportingPeriod;
use Illuminate\Database\Seeder;

/**
 * Gives the seeded reports realistic workflow states + period dates so the
 * Reporting Obligation tabs (Due / Submitted / Pending / Approved / Overdue)
 * have data. Keeps the LATEST period (Q3) approved so dashboard analytics —
 * which use the latest report per indicator — stay populated/correct; only the
 * past periods (Q1/Q2) get a status mix (some unsubmitted → overdue).
 *
 * Run: php artisan db:seed --class=ReportingWorkflowStatesSeeder
 */
class ReportingWorkflowStatesSeeder extends Seeder
{
    public function run(): void
    {
        $periodDates = [
            'Q1 2026' => ['2026-01-01', '2026-03-31', false],
            'Q2 2026' => ['2026-04-01', '2026-06-30', false],
            'Q3 2026' => ['2026-07-01', '2026-09-30', true],
            'Q4 2026' => ['2026-10-01', '2026-12-31', true],
        ];

        foreach ($periodDates as $name => [$start, $end, $open]) {
            ReportingPeriod::where('name', $name)->update([
                'start_date' => $start,
                'end_date' => $end,
                'is_open' => $open,
            ]);
        }

        $pastPeriodIds = ReportingPeriod::whereIn('name', ['Q1 2026', 'Q2 2026'])->pluck('id')->all();

        // Weighted status pool for past-period reports.
        $pool = array_merge(
            array_fill(0, 35, 'approved'),
            array_fill(0, 25, 'pending'),
            array_fill(0, 22, 'draft'),
            array_fill(0, 18, 'returned'),
        );

        foreach (IndicatorReport::whereIn('reporting_period_id', $pastPeriodIds)->get() as $report) {
            $pick = $pool[array_rand($pool)];

            [$status, $submitted, $published] = match ($pick) {
                'approved' => [ReportStatus::Approved, true, true],
                'pending' => [ReportStatus::Pending, true, false],
                'draft' => [ReportStatus::Draft, false, false],
                'returned' => [ReportStatus::Returned, false, false],
            };

            $report->status = $status;
            $report->submitted_at = $submitted ? now()->subDays(rand(20, 90)) : null;
            $report->published_at = $published ? now()->subDays(rand(10, 60)) : null;
            $report->save();
        }

        $this->command->info('Applied reporting workflow states to past-period reports.');
    }
}
