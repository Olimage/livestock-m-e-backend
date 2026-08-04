<?php

namespace App\Http\Controllers;

use App\Models\Lga;
use App\Models\State;
use App\Models\Zone;
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

    public function ApiGetZones(Request $request)
    {
        try {
            $zones = Zone::query()
                ->withCount('states')
                ->when($request->search, fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($request->search).'%']))
                ->orderBy('name')
                ->get(['id', 'uuid', 'name', 'code']);

            return response()->json([
                'status' => true,
                'message' => 'Zones fetched successfully',
                'data' => $this->maybePaginate($zones, $request),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    public function ApiGetStates(Request $request)
    {
        try {
            // Optional now, not required — the management table needs the full
            // list. Passing zone_id still filters exactly as it did when this
            // was a cascading selector, so existing callers are unaffected.
            $request->validate(['zone_id' => 'nullable|exists:zones,id']);

            $states = State::query()
                ->with('zone:id,name,code')
                ->withCount('lgas')
                ->when($request->zone_id, fn ($q) => $q->where('zone_id', $request->zone_id))
                ->when($request->search, fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($request->search).'%']))
                ->orderBy('name')
                ->get(['id', 'uuid', 'name', 'zone_id'])
                ->map(fn (State $state) => [
                    'id' => $state->id,
                    'uuid' => $state->uuid,
                    'name' => $state->name,
                    'zone_id' => $state->zone_id,
                    'zone_code' => $state->zone?->code,
                    'zone_name' => $state->zone?->name,
                    'lgas_count' => $state->lgas_count,
                ]);

            return response()->json([
                'status' => true,
                'message' => 'States fetched successfully',
                'data' => $this->maybePaginate($states, $request),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    public function ApiGetLgas(Request $request)
    {
        try {
            $request->validate([
                'state_id' => 'nullable|exists:states,id',
                'zone_id' => 'nullable|exists:zones,id',
            ]);

            $lgas = Lga::query()
                ->with('state:id,name,zone_id', 'state.zone:id,name,code')
                ->when($request->state_id, fn ($q) => $q->where('state_id', $request->state_id))
                ->when($request->zone_id, fn ($q) => $q->whereHas(
                    'state',
                    fn ($s) => $s->where('zone_id', $request->zone_id)
                ))
                ->when($request->search, fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($request->search).'%']))
                ->orderBy('name')
                ->get(['id', 'uuid', 'name', 'state_id'])
                ->map(fn (Lga $lga) => [
                    'id' => $lga->id,
                    'uuid' => $lga->uuid,
                    'name' => $lga->name,
                    'state_id' => $lga->state_id,
                    'state_name' => $lga->state?->name,
                    'zone_id' => $lga->state?->zone_id,
                    'zone_code' => $lga->state?->zone?->code,
                    'zone_name' => $lga->state?->zone?->name,
                ]);

            return response()->json([
                'status' => true,
                'message' => 'Lgas fetched successfully',
                'data' => $this->maybePaginate($lgas, $request),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * `per_page` absent → the full collection (the geography admin tables filter
     * client-side, and 774 LGAs is ~60 KB). `per_page` present → a paginator, so
     * a future consumer can page without an API change.
     */
    private function maybePaginate(\Illuminate\Support\Collection $items, Request $request)
    {
        if (! $request->filled('per_page')) {
            return $items->values();
        }

        $perPage = max(1, (int) $request->per_page);
        $page = max(1, (int) $request->get('page', 1));

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page
        );
    }

    private function failure(\Throwable $e)
    {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching data',
            'error' => $e->getMessage(),
        ], 500);
    }
}
