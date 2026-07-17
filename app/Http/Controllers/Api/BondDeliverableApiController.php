<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BondDeliverable;
use App\Models\BondOutputIndicator;
use App\Support\IndicatorPerformance;

/**
 * GET /api/v1/bond-deliverables — dashboard bond performance data.
 *
 * @see docs/superpowers/specs/2026-07-16-mems-dashboard-api-contract.md §5
 */
class BondDeliverableApiController extends Controller
{
    public function index()
    {
        try {
            $deliverables = BondDeliverable::with('bondOutputIndicators')
                ->orderBy('id')
                ->get();

            $data = $deliverables->map(function (BondDeliverable $deliverable) {
                $indicators = $deliverable->bondOutputIndicators
                    ->map(fn (BondOutputIndicator $boi) => IndicatorPerformance::present($boi, BondOutputIndicator::class))
                    ->values();

                return [
                    'id' => $deliverable->id,
                    'title' => $deliverable->deliverable,
                    'code' => $deliverable->code,
                    'stats' => $this->rollup($indicators),
                    'indicators' => $indicators,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve bond deliverables',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Per-deliverable status rollup (mirrors the frontend's expected shape):
     * above→onTrack, on→atRisk, below→offTrack.
     *
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $indicators
     * @return array{total: int, onTrack: int, atRisk: int, offTrack: int}
     */
    private function rollup($indicators): array
    {
        $stats = ['total' => $indicators->count(), 'onTrack' => 0, 'atRisk' => 0, 'offTrack' => 0];

        foreach ($indicators as $indicator) {
            match ($indicator['status']) {
                'above' => $stats['onTrack']++,
                'on' => $stats['atRisk']++,
                'below' => $stats['offTrack']++,
                default => null,
            };
        }

        return $stats;
    }
}
