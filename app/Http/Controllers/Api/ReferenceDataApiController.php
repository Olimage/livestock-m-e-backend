<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DisagregationCategory;
use App\Models\DisagregationItem;
use App\Models\NlgasPillar;
use App\Models\SectoralGoal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON CRUD for reference-data entities (NL-GAS Pillars, Sectoral Goals,
 * Disaggregation Categories/Items) for the JWT-authenticated SPA.
 * Session-authenticated Inertia CRUD for the same models lives on
 * ProgramController; validation mirrors it except for the two deliberate
 * divergences below.
 *
 * Divergence 1 — `description` is validated `required|string` here, not
 * `nullable` as ProgramController does. Both `nlgas_pillars.description` and
 * `sectoral_goals.description` are NOT NULL text columns with no default, so
 * ProgramController's `nullable` rule is simply wrong about its own schema:
 * omitting the field there throws a DB exception, not a graceful null. Do
 * not "fix" this back into alignment with ProgramController.
 *
 * Divergence 2 — NlgasPillar payloads omit `uuid` entirely. Only
 * `sectoral_goals` has a `uuid` column (added by a later migration plus a
 * `creating` hook on the model); `nlgas_pillars` never got one. Returning a
 * permanently-null `uuid` for pillars would be worse than not having the key.
 */
class ReferenceDataApiController extends Controller
{
    // ==================== NL-GAS Pillars ====================

    public function indexNlgasPillars(): JsonResponse
    {
        return $this->ok(NlgasPillar::get(['id', 'code', 'title', 'description']), 'NL-GAS pillars.');
    }

    public function storeNlgasPillar(Request $request): JsonResponse
    {
        $pillar = NlgasPillar::create($this->validatePillar($request));

        return $this->ok($pillar->fresh(), 'NL-GAS pillar created.', 201);
    }

    public function updateNlgasPillar(Request $request, NlgasPillar $pillar): JsonResponse
    {
        $pillar->update($this->validatePillar($request, $pillar));

        return $this->ok($pillar->fresh(), 'NL-GAS pillar updated.');
    }

    public function destroyNlgasPillar(NlgasPillar $pillar): JsonResponse
    {
        $pillar->delete();

        return $this->ok(null, 'NL-GAS pillar deleted.');
    }

    // ==================== Sectoral Goals ====================

    public function indexSectoralGoals(): JsonResponse
    {
        return $this->ok(SectoralGoal::get(['id', 'uuid', 'code', 'title', 'description']), 'Sectoral goals.');
    }

    public function storeSectoralGoal(Request $request): JsonResponse
    {
        $goal = SectoralGoal::create($this->validateGoal($request));

        return $this->ok($goal->fresh(), 'Sectoral goal created.', 201);
    }

    public function updateSectoralGoal(Request $request, SectoralGoal $goal): JsonResponse
    {
        $goal->update($this->validateGoal($request, $goal));

        return $this->ok($goal->fresh(), 'Sectoral goal updated.');
    }

    public function destroySectoralGoal(SectoralGoal $goal): JsonResponse
    {
        $goal->delete();

        return $this->ok(null, 'Sectoral goal deleted.');
    }

    // ==================== Disaggregation Categories ====================

    public function indexDisagregationCategories(): JsonResponse
    {
        $categories = DisagregationCategory::with('items:id,disagregation_category_id,name')->get(['id', 'name']);

        return $this->ok($categories, 'Disaggregation categories.');
    }

    public function storeDisagregationCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:disagregation_categories,name',
            'items' => 'nullable|array',
            'items.*' => 'string|max:255',
        ]);

        $category = DisagregationCategory::create(['name' => $validated['name']]);

        foreach (array_filter($validated['items'] ?? []) as $itemName) {
            $category->items()->create(['name' => trim($itemName)]);
        }

        return $this->ok($category->fresh('items'), 'Disaggregation category created.', 201);
    }

    public function updateDisagregationCategory(Request $request, DisagregationCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:disagregation_categories,name,'.$category->id,
        ]);

        $category->update($validated);

        return $this->ok($category->fresh('items'), 'Disaggregation category updated.');
    }

    public function destroyDisagregationCategory(DisagregationCategory $category): JsonResponse
    {
        $category->delete();

        return $this->ok(null, 'Disaggregation category deleted.');
    }

    // ==================== Disaggregation Items ====================

    public function storeDisagregationItem(Request $request, DisagregationCategory $category): JsonResponse
    {
        $item = $category->items()->create($this->validateItem($request));

        return $this->ok($item->fresh(), 'Disaggregation item created.', 201);
    }

    public function updateDisagregationItem(Request $request, DisagregationCategory $category, DisagregationItem $item): JsonResponse
    {
        $item->update($this->validateItem($request));

        return $this->ok($item->fresh(), 'Disaggregation item updated.');
    }

    public function destroyDisagregationItem(DisagregationCategory $category, DisagregationItem $item): JsonResponse
    {
        $item->delete();

        return $this->ok(null, 'Disaggregation item deleted.');
    }

    // ==================== Validation ====================

    private function validatePillar(Request $request, ?NlgasPillar $pillar = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:255|unique:nlgas_pillars,code'.($pillar ? ','.$pillar->id : ''),
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
    }

    private function validateGoal(Request $request, ?SectoralGoal $goal = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:255|unique:sectoral_goals,code'.($goal ? ','.$goal->id : ''),
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
        ]);
    }

    private function ok($data, string $message, int $code = 200): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message, 'data' => $data], $code);
    }
}
