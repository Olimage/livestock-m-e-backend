<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\State;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON write endpoints for the Zone → State → LGA hierarchy, for the
 * JWT-authenticated SPA. Reads live on LocationController alongside the
 * cascading selectors. Validation rules and the two delete guards mirror
 * ZoneController / StateController / LgaController, which serve the same
 * models over session-authenticated Inertia routes.
 */
class GeographyApiController extends Controller
{
    public function storeZone(Request $request): JsonResponse
    {
        $zone = Zone::create($this->validateZone($request));

        return $this->ok($zone->fresh(), 'Zone created.', 201);
    }

    public function updateZone(Request $request, Zone $zone): JsonResponse
    {
        $zone->update($this->validateZone($request));

        return $this->ok($zone->fresh(), 'Zone updated.');
    }

    public function destroyZone(Zone $zone): JsonResponse
    {
        if ($zone->states()->exists()) {
            return $this->refuse('This zone still has states. Move or delete them first.');
        }

        $zone->delete();

        return $this->ok(null, 'Zone deleted.');
    }

    public function storeState(Request $request): JsonResponse
    {
        $state = State::create($this->validateState($request));

        return $this->ok($state->fresh(), 'State created.', 201);
    }

    public function updateState(Request $request, State $state): JsonResponse
    {
        $state->update($this->validateState($request));

        return $this->ok($state->fresh(), 'State updated.');
    }

    public function destroyState(State $state): JsonResponse
    {
        if ($state->lgas()->exists()) {
            return $this->refuse('This state still has LGAs. Move or delete them first.');
        }

        $state->delete();

        return $this->ok(null, 'State deleted.');
    }

    public function storeLga(Request $request): JsonResponse
    {
        $lga = Lga::create($this->validateLga($request));

        return $this->ok($lga->fresh(), 'LGA created.', 201);
    }

    public function updateLga(Request $request, Lga $lga): JsonResponse
    {
        $lga->update($this->validateLga($request));

        return $this->ok($lga->fresh(), 'LGA updated.');
    }

    public function destroyLga(Lga $lga): JsonResponse
    {
        $lga->delete();

        return $this->ok(null, 'LGA deleted.');
    }

    private function validateZone(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);
    }

    private function validateState(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
        ]);
    }

    private function validateLga(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
        ]);
    }

    private function ok($data, string $message, int $code = 200): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message, 'data' => $data], $code);
    }

    private function refuse(string $message): JsonResponse
    {
        return response()->json(['status' => false, 'message' => $message], 422);
    }
}
