<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\State;
use App\Models\Lga;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function zones()
    {
        return response()->json(Zone::select('id', 'name', 'code')->orderBy('name')->get());
    }

    public function states(Request $request)
    {
        $zoneId = $request->query('zone_id');
        $query = State::select('id', 'name', 'zone_id')->orderBy('name');
        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        }
        return response()->json($query->get());
    }

    public function lgas(Request $request)
    {
        $stateId = $request->query('state_id');
        $query = Lga::select('id', 'name', 'state_id')->orderBy('name');
        if ($stateId) {
            $query->where('state_id', $stateId);
        }
        return response()->json($query->get());
    }

    public function ApiGetZones(){

        try{

            $zones =  Zone::select('id', 'name', 'code')->orderBy('name')->get();
            return response()->json([
                'status' => true,
                'message' => 'Zones fetched successfully',
                'data'=> $zones
            ], 200);

        } catch (\Exception $e){

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching data',
                'error' => $e->getMessage()
            ], 500);
        }

    }
    public function ApiGetStates(Request $request  ){

        try{

            $request->validate([
                'zone_id' => 'required|exists:zones,id',
            ]);
            
             $query = State::select('id', 'name', 'zone_id')->orderBy('name')->where('zone_id', $request->zone_id)->get();


            return response()->json([
                'status' => true,
                'message' => 'States fetched successfully',
                'data'=> $query
            ], 200);

        } catch (\Exception $e){

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching data',
                'error' => $e->getMessage()
            ], 500);
        }

    }   
    
    public function ApiGetLgas(Request $request  ){

        try{

            $request->validate([
                'state_id' => 'required|exists:states,id',
            ]);
            
             $query = Lga::select('id', 'name', 'state_id')->orderBy('name')->where('state_id', $request->state_id)->get();

            return response()->json([
                'status' => true,
                'message' => 'Lgas fetched successfully',
                'data'=> $query
            ], 200);

        } catch (\Exception $e){

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching data',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
