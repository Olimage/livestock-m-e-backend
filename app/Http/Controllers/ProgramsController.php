<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indicator;
use App\Models\Module;
use App\Models\SectoralGoal;
use App\Models\PresidentialPriority;

class ProgramsController extends Controller
{
    
public function getIndicators(Request $request)
    {
        try{
        $indicators = Indicator::with('tiers')->get()->makeHidden(['id'])->map(function ($indicator) {
            $indicator->tiers->each->makeHidden(['id', 'pivot', 'created_at', 'updated_at']);
            return $indicator;
        })->makeHidden(['created_at', 'updated_at']);

        return response()->json([
            'status' => true,
            'data' => $indicators
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve indicators',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getSectoralGoals(Request $request)
    {
        try{
        $sectoralGoals = SectoralGoal::with('tiers')->get()->makeHidden(['id'])->map(function ($sectoralGoal) {
            $sectoralGoal->tiers->each->makeHidden(['id', 'pivot', 'created_at', 'updated_at']);
            return $sectoralGoal;
        })->makeHidden(['created_at', 'updated_at']);

        return response()->json([
            'status' => true,
            'data' => $sectoralGoals
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve indicators',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getModules(Request $request)
    {
        try {
            $modules = Module::get()->makeHidden(['id', 'created_at', 'updated_at']);

            return response()->json([
                'status' => true,
                'data' => $modules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve modules',
                'error' => $e->getMessage()
            ], 500);
        }
    }


        public function getPresidentialPriorities(Request $request)
    {
        try{
        $presidentialPriorities = PresidentialPriority::with('tiers')->get()->makeHidden(['id'])->map(function ($presidentialPriority) {
            $presidentialPriority->tiers->each->makeHidden(['id', 'pivot', 'created_at', 'updated_at']);
            return $presidentialPriority ;
        })->makeHidden(['created_at', 'updated_at']);

        return response()->json([
            'status' => true,
            'data' => $presidentialPriorities
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve indicators',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
