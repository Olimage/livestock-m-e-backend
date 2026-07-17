<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use App\Models\SectoralGoal;
use App\Support\IndicatorPerformance;
use Illuminate\Support\Str;

/**
 * Sector outcomes dashboard data.
 *   GET /api/v1/sector-outcomes            — list of sector goals
 *   GET /api/v1/sector-outcomes/{id}/trends — one goal's outcome/output indicators
 *
 * @see docs/superpowers/specs/2026-07-16-mems-dashboard-api-contract.md §4
 */
class SectorOutcomeApiController extends Controller
{
    public function index()
    {
        try {
            $data = SectoralGoal::orderBy('id')->get()->map(fn (SectoralGoal $goal) => [
                'id' => $goal->id,
                'slug' => Str::slug($goal->title),
                'code' => $goal->code,
                'title' => $goal->title,
            ]);

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->fail('Failed to retrieve sector outcomes', $e);
        }
    }

    public function trends(int $id)
    {
        try {
            $goal = SectoralGoal::findOrFail($id);

            // Outcome indicators linked directly to this sector goal.
            $outcomeIndicators = OutcomeIndicator::whereHas(
                'sectoralGoals',
                fn ($q) => $q->where('sectoral_goals.id', $goal->id)
            )->orderBy('code')->get();

            // Output indicators that roll up into those outcome indicators.
            $outcomeIds = $outcomeIndicators->pluck('id');
            $outputIndicators = OutputIndicator::whereHas(
                'outcomeIndicators',
                fn ($q) => $q->whereIn('outcome_indicators.id', $outcomeIds)
            )->orderBy('code')->get();

            $data = [
                'id' => $goal->id,
                'title' => $goal->title,
                'description' => $goal->description,
                'outcomeIndicators' => $outcomeIndicators
                    ->map(fn (OutcomeIndicator $i) => IndicatorPerformance::present($i, OutcomeIndicator::class))
                    ->values(),
                'outputIndicators' => $outputIndicators
                    ->map(fn (OutputIndicator $i) => IndicatorPerformance::present($i, OutputIndicator::class))
                    ->values(),
            ];

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->fail('Failed to retrieve sector outcome trends', $e);
        }
    }

    private function fail(string $message, \Throwable $e)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'error' => $e->getMessage(),
        ], 500);
    }
}
