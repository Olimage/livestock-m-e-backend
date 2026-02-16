<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indicator;
use App\Models\Module;
use App\Models\SectoralGoal;
use App\Models\PresidentialPriority;
use App\Models\BondOutcome;

class ProgramsController extends Controller
{
    
public function getIndicators(Request $request)
{
    try {
        $indicators = Indicator::with('tiers')->get()->makeHidden(['id'])->map(function ($indicator) {
            $indicator->tiers->each->makeHidden(['id', 'pivot', 'created_at', 'updated_at']);
            
            // Transform disaggregation_dimensions to clean array format
            $disagg = $indicator->disaggregation_dimensions;
            if (is_array($disagg) && !empty($disagg)) {
                $cleaned = [];
                foreach ($disagg as $key => $value) {
                    if (is_array($value)) {
                        // Associative element with sub-items
                        $cleaned[$key] = $value;
                    } else {
                        // Simple indexed element
                        $cleaned[] = $value;
                    }
                }
                $indicator->disaggregation_dimensions = $cleaned;
            }
            
            return $indicator;
        })->makeHidden(['created_at', 'updated_at']);

        // Use JSON_FORCE_OBJECT flag selectively or convert to array
        $data = $indicators->toArray();
        
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
        public function getBondOutcomes(Request $request)
    {
        try{
        $bondOutcomes = BondOutcome::with('tiers')->get()->makeHidden(['id'])->map(function ($bondOutcome) {
            $bondOutcome->tiers->each->makeHidden(['id', 'pivot', 'created_at', 'updated_at']);
            return $bondOutcome ;
        })->makeHidden(['created_at', 'updated_at']);

        return response()->json([
            'status' => true,
            'data' => $bondOutcomes
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
