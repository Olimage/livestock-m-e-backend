<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImpactIndicator;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use App\Support\IndicatorPerformance;

/**
 * GET /api/v1/result-chain — result-chain indicators grouped by level
 * (impacts / outcomes / outputs), for the result-chain / sustainability views.
 *
 * @see docs/superpowers/specs/2026-07-16-mems-dashboard-api-contract.md
 */
class ResultChainApiController extends Controller
{
    public function index()
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => [
                    // Keys match the frontend's INDICATORS map (impact is singular).
                    'impact' => $this->level(ImpactIndicator::class),
                    'outcomes' => $this->level(OutcomeIndicator::class),
                    'outputs' => $this->level(OutputIndicator::class),
                    // No activity/input indicator tables exist yet.
                    'activities' => [],
                    'inputs' => [],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve result chain',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param  class-string  $class
     * @return array<int, array<string, mixed>>
     */
    private function level(string $class): array
    {
        return $class::orderBy('code')->get()->map(function ($indicator) use ($class) {
            $block = IndicatorPerformance::present($indicator, $class);

            return [
                'code' => $indicator->code,
                'title' => $indicator->title,
                'status' => $block['status'],
                'actual' => $block['actual'],
                'target' => $block['target'],
                'measurementUnit' => $indicator->getAttribute('measurement_unit'),
            ];
        })->all();
    }
}
