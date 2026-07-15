<?php

namespace App\Http\Controllers;

use App\Models\CrossCuttingMetric;
use App\Models\DisagregationCategory;
use App\Models\DisagregationItem;
use App\Models\IndicatorBaselineYear;
use App\Models\NlgasPillar;
use App\Models\Program;
use App\Models\SectoralGoal;
use App\Support\ResultChainIndicators;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    // ==================== Sectoral Goals ====================
    public function sectoralGoals(Request $request)
    {
        $query = SectoralGoal::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $goals = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)->withQueryString();

        return Inertia::render('Programs/SectoralGoals/Index', [
            'goals' => $goals,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => SectoralGoal::count(),
        ]);
    }

    public function createSectoralGoal()
    {
        return Inertia::render('Programs/SectoralGoals/Create');
    }

    public function storeSectoralGoal(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:sectoral_goals',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        SectoralGoal::create($validated);

        return redirect()->route('programs.sectoral-goals.index')
            ->with('success', 'Sectoral Goal created successfully');
    }

    public function editSectoralGoal(SectoralGoal $goal)
    {
        return Inertia::render('Programs/SectoralGoals/Edit', [
            'goal' => $goal,
        ]);
    }

    public function updateSectoralGoal(Request $request, SectoralGoal $goal)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:sectoral_goals,code,'.$goal->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $goal->update($validated);

        return redirect()->route('programs.sectoral-goals.index')
            ->with('success', 'Sectoral Goal updated successfully');
    }

    public function destroySectoralGoal(SectoralGoal $goal)
    {
        $goal->delete();

        return redirect()->route('programs.sectoral-goals.index')
            ->with('success', 'Sectoral Goal deleted successfully');
    }

    // ==================== NLGAS Pillars ====================
    public function nlgasPillars(Request $request)
    {
        $query = NlgasPillar::query()->with(['programs']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $pillars = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)->withQueryString();

        return Inertia::render('Programs/NlgasPillars/Index', [
            'pillars' => $pillars,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => NlgasPillar::count(),
        ]);
    }

    public function createNlgasPillar()
    {
        return Inertia::render('Programs/NlgasPillars/Create');
    }

    public function storeNlgasPillar(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:nlgas_pillars',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        NlgasPillar::create($validated);

        return redirect()->route('programs.nlgas-pillars.index')
            ->with('success', 'NLGAS Pillar created successfully');
    }

    public function editNlgasPillar(NlgasPillar $pillar)
    {
        return Inertia::render('Programs/NlgasPillars/Edit', [
            'pillar' => $pillar,
        ]);
    }

    public function updateNlgasPillar(Request $request, NlgasPillar $pillar)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:nlgas_pillars,code,'.$pillar->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $pillar->update($validated);

        return redirect()->route('programs.nlgas-pillars.index')
            ->with('success', 'NLGAS Pillar updated successfully');
    }

    public function destroyNlgasPillar(NlgasPillar $pillar)
    {
        $pillar->delete();

        return redirect()->route('programs.nlgas-pillars.index')
            ->with('success', 'NLGAS Pillar deleted successfully');
    }

    // ==================== Programs ====================
    public function programs(Request $request)
    {
        $query = Program::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)->withQueryString();

        return Inertia::render('Programs/Programs/Index', [
            'programs' => $programs,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => Program::count(),
        ]);
    }

    public function createProgram()
    {
        return Inertia::render('Programs/Programs/Create');
    }

    public function storeProgram(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:programs',
            'title' => 'required|string|max:255',
        ]);

        Program::create($validated);

        return redirect()->route('programs.programs.index')
            ->with('success', 'Program created successfully');
    }

    public function editProgram(Program $program)
    {
        return Inertia::render('Programs/Programs/Edit', [
            'program' => $program,
        ]);
    }

    public function updateProgram(Request $request, Program $program)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:programs,code,'.$program->id,
            'title' => 'required|string|max:255',
        ]);

        $program->update($validated);

        return redirect()->route('programs.programs.index')
            ->with('success', 'Program updated successfully');
    }

    public function destroyProgram(Program $program)
    {
        $program->delete();

        return redirect()->route('programs.programs.index')
            ->with('success', 'Program deleted successfully');
    }

    // ==================== Cross-Cutting Metrics ====================
    public function crossCuttingMetrics(Request $request)
    {
        $query = CrossCuttingMetric::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%")
                    ->orWhere('key_metric', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $metrics = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)->withQueryString();

        return Inertia::render('Programs/CrossCuttingMetrics/Index', [
            'metrics' => $metrics,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => CrossCuttingMetric::count(),
        ]);
    }

    public function createCrossCuttingMetric()
    {
        return Inertia::render('Programs/CrossCuttingMetrics/Create');
    }

    public function storeCrossCuttingMetric(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:cross_cutting_metrics',
            'area' => 'required|string|max:255',
            'key_metric' => 'required|string|max:255',
            'purpose' => 'required|string',
        ]);

        CrossCuttingMetric::create($validated);

        return redirect()->route('programs.cross-cutting-metrics.index')
            ->with('success', 'Cross-Cutting Metric created successfully');
    }

    public function editCrossCuttingMetric(CrossCuttingMetric $crossCuttingMetric)
    {
        return Inertia::render('Programs/CrossCuttingMetrics/Edit', [
            'metric' => $crossCuttingMetric,
        ]);
    }

    public function updateCrossCuttingMetric(Request $request, CrossCuttingMetric $crossCuttingMetric)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:cross_cutting_metrics,code,'.$crossCuttingMetric->id,
            'area' => 'required|string|max:255',
            'key_metric' => 'required|string|max:255',
            'purpose' => 'required|string',
        ]);

        $crossCuttingMetric->update($validated);

        return redirect()->route('programs.cross-cutting-metrics.index')
            ->with('success', 'Cross-Cutting Metric updated successfully');
    }

    public function destroyCrossCuttingMetric(CrossCuttingMetric $crossCuttingMetric)
    {
        $crossCuttingMetric->delete();

        return redirect()->route('programs.cross-cutting-metrics.index')
            ->with('success', 'Cross-Cutting Metric deleted successfully');
    }

    // ==================== Disaggregation Categories ====================

    public function disagregationCategories(Request $request)
    {
        $query = DisagregationCategory::withCount('items');

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15)->withQueryString();

        return Inertia::render('Programs/Disagregations/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => DisagregationCategory::count(),
        ]);
    }

    public function createDisagregationCategory()
    {
        return Inertia::render('Programs/Disagregations/Create');
    }

    public function storeDisagregationCategory(Request $request)
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

        return redirect()->route('programs.disagregations.index')
            ->with('success', 'Disaggregation category created successfully');
    }

    public function editDisagregationCategory(DisagregationCategory $category)
    {
        return Inertia::render('Programs/Disagregations/Edit', [
            'category' => $category->load('items'),
        ]);
    }

    public function updateDisagregationCategory(Request $request, DisagregationCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:disagregation_categories,name,'.$category->id,
        ]);

        $category->update($validated);

        return redirect()->route('programs.disagregations.edit', $category->id)
            ->with('success', 'Category updated successfully');
    }

    public function destroyDisagregationCategory(DisagregationCategory $category)
    {
        $category->delete();

        return redirect()->route('programs.disagregations.index')
            ->with('success', 'Disaggregation category deleted successfully');
    }

    // ==================== Disaggregation Items ====================

    public function storeDisagregationItem(Request $request, DisagregationCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->items()->create($validated);

        return redirect()->route('programs.disagregations.edit', $category->id)
            ->with('success', 'Item added successfully');
    }

    public function updateDisagregationItem(Request $request, DisagregationCategory $category, DisagregationItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('programs.disagregations.edit', $category->id)
            ->with('success', 'Item updated successfully');
    }

    public function destroyDisagregationItem(DisagregationCategory $category, DisagregationItem $item)
    {
        $item->delete();

        return redirect()->route('programs.disagregations.edit', $category->id)
            ->with('success', 'Item deleted successfully');
    }

    // ==================== Indicator Baseline Years ====================

    public function baselines(Request $request)
    {
        $query = IndicatorBaselineYear::with('indicatorable:id,code,title');

        if ($request->search) {
            $search = strtolower($request->search);
        }

        $baselines = $query
            ->orderBy($request->sort_by ?? 'baseline_year', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 15)->withQueryString();

        // Optional in-memory search on the resolved indicator code/title.
        if (! empty($search)) {
            $baselines->setCollection(
                $baselines->getCollection()->filter(function ($b) use ($search) {
                    $code = strtolower($b->indicatorable->code ?? '');
                    $title = strtolower($b->indicatorable->title ?? '');

                    return str_contains($code, $search) || str_contains($title, $search);
                })->values()
            );
        }

        return Inertia::render('Programs/Baselines/Index', [
            'baselines' => $baselines,
            'indicators' => ResultChainIndicators::options(),
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => IndicatorBaselineYear::count(),
        ]);
    }

    public function createBaseline()
    {
        return Inertia::render('Programs/Baselines/Create', [
            'indicators' => ResultChainIndicators::options(),
        ]);
    }

    public function storeBaseline(Request $request)
    {
        $request->validate([
            'indicatorable_type' => 'required|string',
            'indicatorable_id' => 'required|integer',
            'baseline_year' => 'nullable|integer|min:1900|max:2100',
            'target_year' => 'nullable|integer|min:1900|max:2100',
            'baseline' => 'required|numeric',
            'target' => 'required|numeric',
            'actual' => 'required|numeric',
        ]);

        abort_unless(
            in_array($request->indicatorable_type, array_keys(ResultChainIndicators::TYPES), true),
            422
        );

        IndicatorBaselineYear::create($request->only([
            'indicatorable_id', 'indicatorable_type', 'baseline_year', 'target_year', 'baseline', 'target', 'actual',
        ]));

        return redirect()->route('programs.baselines.index')
            ->with('success', 'Baseline year added successfully');
    }

    public function editBaseline(IndicatorBaselineYear $baseline)
    {
        return Inertia::render('Programs/Baselines/Edit', [
            'baseline' => $baseline->load('indicatorable:id,code,title'),
            'indicators' => ResultChainIndicators::options(),
        ]);
    }

    public function updateBaseline(Request $request, IndicatorBaselineYear $baseline)
    {
        $request->validate([
            'indicatorable_type' => 'required|string',
            'indicatorable_id' => 'required|integer',
            'baseline_year' => 'nullable|integer|min:1900|max:2100',
            'target_year' => 'nullable|integer|min:1900|max:2100',
            'baseline' => 'required|numeric',
            'target' => 'required|numeric',
            'actual' => 'required|numeric',
        ]);

        abort_unless(
            in_array($request->indicatorable_type, array_keys(ResultChainIndicators::TYPES), true),
            422
        );

        $baseline->update($request->only([
            'indicatorable_id', 'indicatorable_type', 'baseline_year', 'target_year', 'baseline', 'target', 'actual',
        ]));

        return redirect()->route('programs.baselines.index')
            ->with('success', 'Baseline year updated successfully');
    }

    public function destroyBaseline(IndicatorBaselineYear $baseline)
    {
        $baseline->delete();

        return redirect()->route('programs.baselines.index')
            ->with('success', 'Baseline year deleted successfully');
    }
}
