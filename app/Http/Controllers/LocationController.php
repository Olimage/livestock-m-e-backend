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
}
