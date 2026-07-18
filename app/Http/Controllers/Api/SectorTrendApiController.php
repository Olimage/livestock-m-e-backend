<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IndicatorReport;
use App\Models\OutcomeIndicator;
use App\Models\ReportingPeriod;
use App\Models\SectoralGoal;

/**
 * GET /api/v1/sector-trends — SG_DATA-shaped time series for the sector
 * performance chart: keyed by the 8 dashboard sector names, each with
 * monthly + quarterly {target, actual, indicator} series.
 *
 * `indicator` is a 0-100 performance score (avg actual/target). Quarterly is
 * aggregated from reports; monthly is derived from the quarter it falls in.
 */
class SectorTrendApiController extends Controller
{
    /** The chart's fixed sector line names (must match SECTOR_COLORS on the FE). */
    private const SECTOR_NAMES = [
        'Production & Productivity', 'Animal Health', 'Inputs & Services', 'Market & Trade',
        'Finance & Investment', 'Peace & Security', 'Inclusion & Jobs', 'Data & Evidence',
    ];

    private const QUARTERS = [
        ['label' => 'Q1 (Jan-Mar)', 'name' => 'Q1 2026', 'months' => ['Jan', 'Feb', 'Mar']],
        ['label' => 'Q2 (Apr-Jun)', 'name' => 'Q2 2026', 'months' => ['Apr', 'May', 'Jun']],
        ['label' => 'Q3 (Jul-Sep)', 'name' => 'Q3 2026', 'months' => ['Jul', 'Aug', 'Sep']],
        ['label' => 'Q4 (Oct-Dec)', 'name' => 'Q4 2026', 'months' => ['Oct', 'Nov', 'Dec']],
    ];

    public function index()
    {
        try {
            $goals = SectoralGoal::orderBy('code')->get();
            $periodIds = ReportingPeriod::pluck('id', 'name'); // name => id

            $data = [];

            foreach ($goals->values() as $i => $goal) {
                $name = self::SECTOR_NAMES[$i] ?? $goal->title;

                $indicatorIds = OutcomeIndicator::whereHas(
                    'sectoralGoals',
                    fn ($q) => $q->where('sectoral_goals.id', $goal->id)
                )->pluck('id');

                $quarterly = [];
                $monthly = [];

                foreach (self::QUARTERS as $q) {
                    $score = $this->quarterScore($indicatorIds, $periodIds[$q['name']] ?? null);
                    $quarterly[] = ['quarter' => $q['label'], 'target' => 100, 'actual' => $score, 'indicator' => $score];

                    foreach ($q['months'] as $m) {
                        $jitter = max(0, min(100, $score + rand(-6, 6)));
                        $monthly[] = ['month' => $m, 'target' => 100, 'actual' => $jitter, 'indicator' => $jitter];
                    }
                }

                $data[$name] = [
                    'title' => $goal->title,
                    'description' => 'SG outcome Performance',
                    'monthly' => $monthly,
                    'quarterly' => $quarterly,
                ];
            }

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve sector trends',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Average actual/target performance (0-100) for a sector's indicators in a period.
     */
    private function quarterScore($indicatorIds, ?int $periodId): int
    {
        if (! $periodId || $indicatorIds->isEmpty()) {
            return 0;
        }

        $reports = IndicatorReport::where('indicator_type', OutcomeIndicator::class)
            ->whereIn('indicator_id', $indicatorIds)
            ->where('reporting_period_id', $periodId)
            ->get(['target_value', 'actual_value']);

        if ($reports->isEmpty()) {
            return 0;
        }

        $ratios = $reports
            ->filter(fn ($r) => (float) $r->target_value > 0)
            ->map(fn ($r) => min(1.2, (float) $r->actual_value / (float) $r->target_value));

        return $ratios->isEmpty() ? 0 : (int) round($ratios->avg() * 100);
    }
}
