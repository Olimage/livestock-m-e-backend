<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indicator;
use App\Models\Module;

class ProgramsController extends Controller
{
    
public function getIndaicators(Request $request)
    {
        try{
        $indicators = Indicator::with('tiers')->get()->makeHidden(['id'])->map(function ($indicator) {
            $indicator->tiers->each->makeHidden(['id', 'pivot', 'created_at', 'updated_at']);
            return $indicator;
        });

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
}
