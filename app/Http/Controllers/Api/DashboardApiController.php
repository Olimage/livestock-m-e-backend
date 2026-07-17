<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BondOutputIndicator;
use App\Models\ImpactIndicator;
use App\Models\IndicatorReport;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use App\Support\IndicatorPerformance;

/**
 * Executive dashboard rollups.
 *   GET /api/v1/dashboard/overview — status bucket counts
 *   GET /api/v1/dashboard/alerts   — attention items
 *
 * Buckets are derived from the latest report per indicator (health_status).
 *
 * @see docs/superpowers/specs/2026-07-16-mems-dashboard-api-contract.md §3
 */
class DashboardApiController extends Controller
{
    public function overview()
    {
        try {
            $latest = $this->latestReports();

            $onTrack = $atRisk = $offTrack = 0;
            foreach ($latest as $r) {
                match ($this->health($r->actual_value, $r->target_value)) {
                    'on_track' => $onTrack++,
                    'at_risk' => $atRisk++,
                    default => $offTrack++,
                };
            }

            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => [
                    'totalPrograms' => $onTrack + $atRisk + $offTrack,
                    'onTrack' => $onTrack,
                    'atRisk' => $atRisk,
                    'offTrack' => $offTrack,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->fail('Failed to retrieve dashboard overview', $e);
        }
    }

    public function alerts()
    {
        try {
            $alerts = [];
            $id = 1;

            foreach ($this->latestReports() as $r) {
                if ($this->health($r->actual_value, $r->target_value) === 'off_track') {
                    $alerts[] = [
                        'id' => $id++,
                        'type' => 'warning',
                        'message' => ($r->indicator_code ?? 'Indicator').' is below target',
                    ];
                }
                if (count($alerts) >= 10) {
                    break;
                }
            }

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $alerts]);
        } catch (\Throwable $e) {
            return $this->fail('Failed to retrieve dashboard alerts', $e);
        }
    }

    /**
     * Per-indicator-type target-status breakdown (Above/On/Below %), for the
     * executive dashboard status cards. Computed from the latest report per indicator.
     */
    public function statusBreakdown()
    {
        try {
            $latest = $this->latestReports();

            $groups = [
                'impact' => ImpactIndicator::class,
                'outcome' => OutcomeIndicator::class,
                'output' => OutputIndicator::class,
                'bond' => BondOutputIndicator::class,
            ];

            $data = [];
            foreach ($groups as $key => $class) {
                $data[$key] = $this->bucket($latest->where('indicator_type', $class));
            }

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->fail('Failed to retrieve status breakdown', $e);
        }
    }

    /**
     * @return array{aboveTarget: int, onTarget: int, belowTarget: int, total: int}
     */
    private function bucket($rows): array
    {
        $total = $rows->count();
        if ($total === 0) {
            return ['aboveTarget' => 0, 'onTarget' => 0, 'belowTarget' => 0, 'total' => 0];
        }

        $above = $on = $below = 0;
        foreach ($rows as $r) {
            $actual = $r->actual_value !== null ? (float) $r->actual_value : null;
            $target = $r->target_value !== null ? (float) $r->target_value : null;
            match (IndicatorPerformance::status($actual, $target)) {
                'above' => $above++,
                'on' => $on++,
                'below' => $below++,
                default => null,
            };
        }

        return [
            'aboveTarget' => (int) round($above / $total * 100),
            'onTarget' => (int) round($on / $total * 100),
            'belowTarget' => (int) round($below / $total * 100),
            'total' => $total,
        ];
    }

    /**
     * Latest report per (indicator_type, indicator_id), most recent period wins.
     */
    private function latestReports()
    {
        return IndicatorReport::orderByDesc('reporting_period_id')
            ->get(['indicator_type', 'indicator_id', 'indicator_code', 'target_value', 'actual_value'])
            ->unique(fn ($r) => $r->indicator_type.':'.$r->indicator_id)
            ->values();
    }

    private function health($actual, $target): string
    {
        if ($target === null || (float) $target == 0.0 || $actual === null) {
            return 'off_track';
        }

        $ratio = (float) $actual / (float) $target;

        return match (true) {
            $ratio >= 0.90 => 'on_track',
            $ratio >= 0.75 => 'at_risk',
            default => 'off_track',
        };
    }

    private function fail(string $message, \Throwable $e)
    {
        return response()->json(['status' => false, 'message' => $message, 'error' => $e->getMessage()], 500);
    }
}
