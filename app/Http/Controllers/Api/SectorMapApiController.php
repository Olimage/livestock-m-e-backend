<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NlgasPillar;

/**
 * GET /api/v1/sector-map — the PILLAR_PROGRAMS matrix for the Nigeria map:
 * { [pillarTitle]: { [stateName]: { active, programs[], output } } }, derived
 * from each program's seeded coverage_states.
 */
class SectorMapApiController extends Controller
{
    public function index()
    {
        try {
            $pillars = NlgasPillar::with(['programs' => fn ($q) => $q->orderBy('code')])
                ->orderBy('code')
                ->get();

            $data = [];

            foreach ($pillars as $pillar) {
                $byState = [];

                foreach ($pillar->programs as $program) {
                    foreach ($program->coverage_states ?? [] as $state) {
                        if (! isset($byState[$state])) {
                            $byState[$state] = ['active' => true, 'programs' => [], 'output' => $program->title];
                        }
                        $byState[$state]['programs'][] = $program->title;
                    }
                }

                if (! empty($byState)) {
                    $data[$pillar->title] = $byState;
                }
            }

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve sector map',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
