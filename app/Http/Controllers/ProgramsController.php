<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\SectoralGoal;
use App\Support\ResultChainIndicators;
use Illuminate\Http\Request;

class ProgramsController extends Controller
{
    public function getIndicators(Request $request)
    {
        try {
            return response()->json([
                'status' => true,
                'data' => ResultChainIndicators::options(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve indicators',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSectoralGoals(Request $request)
    {
        try {
            $sectoralGoals = SectoralGoal::get()->makeHidden(['id', 'created_at', 'updated_at']);

            return response()->json([
                'status' => true,
                'data' => $sectoralGoals,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve indicators',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getModules(Request $request)
    {
        try {
            $modules = Module::get()->makeHidden(['id', 'created_at', 'updated_at']);

            return response()->json([
                'status' => true,
                'data' => $modules,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve modules',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
