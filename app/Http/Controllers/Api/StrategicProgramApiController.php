<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NlgasPillar;
use App\Models\Program;

/**
 * GET /api/v1/strategic-programs — NLGAS pillars and their programs, with
 * per-program owner, finances, coverage, and a status derived from finance
 * execution (actual vs planned).
 *
 * @see docs/superpowers/specs/2026-07-16-mems-dashboard-api-contract.md §6
 */
class StrategicProgramApiController extends Controller
{
    public function index()
    {
        try {
            $pillars = NlgasPillar::with(['programs' => fn ($q) => $q->orderBy('code')])
                ->orderBy('code')
                ->get();

            $data = $pillars->map(fn (NlgasPillar $pillar) => [
                'id' => $pillar->id,
                'code' => $pillar->code,
                'title' => $pillar->title,
                'objective' => $pillar->description,
                'indicators' => $pillar->programs->map(fn (Program $program) => [
                    'id' => $program->id,
                    'programName' => $program->title,
                    'code' => $program->code,
                    'status' => $this->financeStatus($program),
                    'owner' => $program->owner,
                    'finances' => [
                        'planned' => $program->planned_amount !== null ? (float) $program->planned_amount : null,
                        'actual' => $program->actual_amount !== null ? (float) $program->actual_amount : null,
                        'currency' => 'NGN',
                    ],
                    'coverage' => $program->coverage,
                    'coverageStates' => $program->coverage_states ?? [],
                ])->values(),
            ]);

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve strategic programs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Status from finance execution ratio (actual / planned): machine value.
     */
    private function financeStatus(Program $program): string
    {
        $planned = (float) $program->planned_amount;
        $actual = (float) $program->actual_amount;

        if ($planned <= 0) {
            return 'unknown';
        }

        $ratio = $actual / $planned;

        return match (true) {
            $ratio >= 0.85 => 'above',
            $ratio >= 0.50 => 'on',
            default => 'below',
        };
    }
}
