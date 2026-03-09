<?php

namespace App\Http\Controllers;

use App\Models\PresidentialPriority;
use App\Models\SectoralGoal;
use App\Models\NlgasPillar;
use App\Models\Program;
use App\Models\Tier;
use App\Models\Indicator;
use App\Models\CrossCuttingMetric;
use App\Models\Department;
use App\Models\DisagregationCategory;
use App\Models\DisagregationItem;
use App\Models\IndicatorBaselineYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    // ==================== Presidential Priorities ====================
    public function presidentialPriorities(Request $request)
    {
        $query = PresidentialPriority::query()->with(['tiers']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $priorities = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                           ->paginate($request->per_page ?? 10);

        return Inertia::render('Programs/PresidentialPriorities/Index', [
            'priorities' => $priorities,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => PresidentialPriority::count()
        ]);
    }

    public function createPresidentialPriority()
    {
        return Inertia::render('Programs/PresidentialPriorities/Create', [
            'tiers' => Tier::all()
        ]);
    }

    public function storePresidentialPriority(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:presidential_priorities',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $priority = PresidentialPriority::create($validated);

        if ($request->has('tier_ids')) {
            $priority->tiers()->attach($request->tier_ids);
        }

        return redirect()->route('programs.presidential-priorities.index')
                        ->with('success', 'Presidential Priority created successfully');
    }

    public function editPresidentialPriority(PresidentialPriority $priority)
    {
        return Inertia::render('Programs/PresidentialPriorities/Edit', [
            'priority' => $priority->load(['tiers']),
            'tiers' => Tier::all()
        ]);
    }

    public function updatePresidentialPriority(Request $request, PresidentialPriority $priority)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:presidential_priorities,code,' . $priority->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $priority->update($validated);
        $priority->tiers()->sync($request->tier_ids ?? []);

        return redirect()->route('programs.presidential-priorities.index')
                        ->with('success', 'Presidential Priority updated successfully');
    }

    public function destroyPresidentialPriority(PresidentialPriority $priority)
    {
        $priority->delete();
        return redirect()->route('programs.presidential-priorities.index')
                        ->with('success', 'Presidential Priority deleted successfully');
    }

    // ==================== Sectoral Goals ====================
    public function sectoralGoals(Request $request)
    {
        $query = SectoralGoal::query()->with(['tiers']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $goals = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                      ->paginate($request->per_page ?? 10);

        return Inertia::render('Programs/SectoralGoals/Index', [
            'goals' => $goals,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => SectoralGoal::count()
        ]);
    }

    public function createSectoralGoal()
    {
        return Inertia::render('Programs/SectoralGoals/Create', [
            'tiers' => Tier::all()
        ]);
    }

    public function storeSectoralGoal(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:sectoral_goals',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $goal = SectoralGoal::create($validated);

        if ($request->has('tier_ids')) {
            $goal->tiers()->attach($request->tier_ids);
        }

        return redirect()->route('programs.sectoral-goals.index')
                        ->with('success', 'Sectoral Goal created successfully');
    }

    public function editSectoralGoal(SectoralGoal $goal)
    {
        return Inertia::render('Programs/SectoralGoals/Edit', [
            'goal' => $goal->load(['tiers']),
            'tiers' => Tier::all()
        ]);
    }

    public function updateSectoralGoal(Request $request, SectoralGoal $goal)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:sectoral_goals,code,' . $goal->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $goal->update($validated);
        $goal->tiers()->sync($request->tier_ids ?? []);

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
        $query = NlgasPillar::query()->with(['tiers', 'programs']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $pillars = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                        ->paginate($request->per_page ?? 10);

        return Inertia::render('Programs/NlgasPillars/Index', [
            'pillars' => $pillars,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => NlgasPillar::count()
        ]);
    }

    public function createNlgasPillar()
    {
        return Inertia::render('Programs/NlgasPillars/Create', [
            'tiers' => Tier::all()
        ]);
    }

    public function storeNlgasPillar(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:nlgas_pillars',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $pillar = NlgasPillar::create($validated);

        if ($request->has('tier_ids')) {
            $pillar->tiers()->attach($request->tier_ids);
        }

        return redirect()->route('programs.nlgas-pillars.index')
                        ->with('success', 'NLGAS Pillar created successfully');
    }

    public function editNlgasPillar(NlgasPillar $pillar)
    {
        return Inertia::render('Programs/NlgasPillars/Edit', [
            'pillar' => $pillar->load(['tiers']),
            'tiers' => Tier::all()
        ]);
    }

    public function updateNlgasPillar(Request $request, NlgasPillar $pillar)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:nlgas_pillars,code,' . $pillar->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $pillar->update($validated);
        $pillar->tiers()->sync($request->tier_ids ?? []);

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
        $query = Program::query()->with(['nlgasPillar']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('pillar_id')) {
            $query->where('nlgas_pillar_id', $request->pillar_id);
        }

        $programs = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                         ->paginate($request->per_page ?? 10);

        return Inertia::render('Programs/Programs/Index', [
            'programs' => $programs,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order', 'pillar_id']),
            'pillars' => NlgasPillar::all(),
            'totalCount' => Program::count()
        ]);
    }

    public function createProgram()
    {
        return Inertia::render('Programs/Programs/Create', [
            'pillars' => NlgasPillar::all()
        ]);
    }

    public function storeProgram(Request $request)
    {
        $validated = $request->validate([
            'nlgas_pillar_id' => 'required|exists:nlgas_pillars,id',
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
            'program' => $program->load(['nlgasPillar']),
            'pillars' => NlgasPillar::all()
        ]);
    }

    public function updateProgram(Request $request, Program $program)
    {
        $validated = $request->validate([
            'nlgas_pillar_id' => 'required|exists:nlgas_pillars,id',
            'code' => 'required|string|max:255|unique:programs,code,' . $program->id,
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

    // ==================== Indicators ====================
    public function indicators(Request $request)
    {
        $query = Indicator::query()
            ->with(['tiers', 'mainDepartment:id,name'])
            ->withCount([
                'disagregation as disagregation_count',
                'supportingDepartments as supporting_departments_count',
            ]);

        if ($request->search) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->indicator_type) {
            $query->where('indicator_type', $request->indicator_type);
        }

        $indicators = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                           ->paginate($request->per_page ?? 10);

        return Inertia::render('Programs/Indicators/Index', [
            'indicators' => $indicators,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order', 'indicator_type']),
            'totalCount' => Indicator::count()
        ]);
    }

    public function createIndicator()
    {
        return Inertia::render('Programs/Indicators/Create', [
            'tiers'                   => Tier::all(),
            'departments'             => Department::orderBy('name')->get(['id', 'name']),
            'sectoralGoals'           => SectoralGoal::orderBy('code')->get(['id', 'code', 'title', 'description']),
            'disagregationCategories' => DisagregationCategory::with('items:id,disagregation_category_id,name')
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeIndicator(Request $request)
    {
        $request->validate([
            'code'                        => 'required|string|max:255|unique:indicators',
            'title'                       => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'indicator_type'              => 'required|in:outcome,output,impact',
            'measurement_unit'                      => 'nullable|string',
            'collection_frequency'                  => 'nullable|string',
            'reporting_frequency'                   => 'nullable|string',
            'tier_ids'                              => 'nullable|array',
            'sectoral_goal_ids'                     => 'nullable|array',
            'sectoral_goal_ids.*'                   => 'exists:sectoral_goals,id',
            'main_department_id'                    => 'nullable|exists:departments,id',
            'supporting_department_ids'             => 'nullable|array',
            'supporting_department_ids.*'           => 'exists:departments,id',
            'disagregation_item_ids'                => 'nullable|array',
            'new_disagregation_categories'          => 'nullable|array',
            'new_disagregation_categories.*.name'   => 'required|string|max:255',
            'new_disagregation_categories.*.items'  => 'nullable|array',
            'new_disagregation_categories.*.items.*' => 'string|max:255',
        ]);

        $indicator = Indicator::create($request->only([
            'code', 'title', 'description', 'indicator_type', 'measurement_unit',
            'collection_frequency', 'reporting_frequency',
        ]));

        $indicator->tiers()->sync($request->tier_ids ?? []);
        $indicator->sectoralGoals()->sync($request->sectoral_goal_ids ?? []);

        $newItemIds = [];
        foreach ($request->new_disagregation_categories ?? [] as $cat) {
            if (!empty($cat['name'])) {
                $category = DisagregationCategory::firstOrCreate(['name' => trim($cat['name'])]);
                foreach ($cat['items'] ?? [] as $itemName) {
                    if (!empty(trim($itemName))) {
                        $item = $category->items()->firstOrCreate(['name' => trim($itemName)]);
                        $newItemIds[] = $item->id;
                    }
                }
            }
        }
        $indicator->disagregation()->sync(array_merge($request->disagregation_item_ids ?? [], $newItemIds));

        $deptSync = [];
        if ($request->main_department_id) {
            $deptSync[$request->main_department_id] = ['role' => 'main'];
        }
        foreach ($request->supporting_department_ids ?? [] as $id) {
            if ($id != $request->main_department_id) {
                $deptSync[$id] = ['role' => 'supporting'];
            }
        }
        $indicator->departments()->sync($deptSync);

        return redirect()->route('programs.indicators.index')
                        ->with('success', 'Indicator created successfully');
    }

    public function editIndicator(Indicator $indicator)
    {
        $indicator->load(['tiers', 'sectoralGoals', 'mainDepartment', 'supportingDepartments']);

        return Inertia::render('Programs/Indicators/Edit', [
            'indicator'               => $indicator,
            'tiers'                   => Tier::all(),
            'departments'             => Department::orderBy('name')->get(['id', 'name']),
            'sectoralGoals'           => SectoralGoal::orderBy('code')->get(['id', 'code', 'title', 'description']),
            'disagregationCategories' => DisagregationCategory::with('items:id,disagregation_category_id,name')
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function updateIndicator(Request $request, Indicator $indicator)
    {
        $request->validate([
            'code'                        => 'required|string|max:255|unique:indicators,code,' . $indicator->id,
            'title'                       => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'indicator_type'              => 'required|in:outcome,output,impact',
            'measurement_unit'                      => 'nullable|string',
            'collection_frequency'                  => 'nullable|string',
            'reporting_frequency'                   => 'nullable|string',
            'tier_ids'                              => 'nullable|array',
            'sectoral_goal_ids'                     => 'nullable|array',
            'sectoral_goal_ids.*'                   => 'exists:sectoral_goals,id',
            'main_department_id'                    => 'nullable|exists:departments,id',
            'supporting_department_ids'             => 'nullable|array',
            'supporting_department_ids.*'           => 'exists:departments,id',
            'disagregation_item_ids'                => 'nullable|array',
            'new_disagregation_categories'          => 'nullable|array',
            'new_disagregation_categories.*.name'   => 'required|string|max:255',
            'new_disagregation_categories.*.items'  => 'nullable|array',
            'new_disagregation_categories.*.items.*' => 'string|max:255',
        ]);

        $indicator->update($request->only([
            'code', 'title', 'description', 'indicator_type', 'measurement_unit',
            'collection_frequency', 'reporting_frequency',
        ]));

        $indicator->tiers()->sync($request->tier_ids ?? []);
        $indicator->sectoralGoals()->sync($request->sectoral_goal_ids ?? []);

        $newItemIds = [];
        foreach ($request->new_disagregation_categories ?? [] as $cat) {
            if (!empty($cat['name'])) {
                $category = DisagregationCategory::firstOrCreate(['name' => trim($cat['name'])]);
                foreach ($cat['items'] ?? [] as $itemName) {
                    if (!empty(trim($itemName))) {
                        $item = $category->items()->firstOrCreate(['name' => trim($itemName)]);
                        $newItemIds[] = $item->id;
                    }
                }
            }
        }
        $indicator->disagregation()->sync(array_merge($request->disagregation_item_ids ?? [], $newItemIds));

        $deptSync = [];
        if ($request->main_department_id) {
            $deptSync[$request->main_department_id] = ['role' => 'main'];
        }
        foreach ($request->supporting_department_ids ?? [] as $id) {
            if ($id != $request->main_department_id) {
                $deptSync[$id] = ['role' => 'supporting'];
            }
        }
        $indicator->departments()->sync($deptSync);

        return redirect()->route('programs.indicators.index')
                        ->with('success', 'Indicator updated successfully');
    }

    public function destroyIndicator(Indicator $indicator)
    {
        $indicator->delete();
        return redirect()->route('programs.indicators.index')
                        ->with('success', 'Indicator deleted successfully');
    }

    // ==================== Tiers ====================
    public function tiers(Request $request)
    {
        $query = Tier::query()
            ->withCount(['indicators', 'sectoralGoals', 'presidentialPriorities']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tier', 'like', "%{$search}%")
                  ->orWhere('level', 'like', "%{$search}%")
                  ->orWhere('attribution', 'like', "%{$search}%");
            });
        }

        $tiers = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                      ->paginate($request->per_page ?? 10);

        return Inertia::render('Programs/Tiers/Index', [
            'tiers' => $tiers,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => Tier::count()
        ]);
    }

    public function createTier()
    {
        return Inertia::render('Programs/Tiers/Create');
    }

    public function storeTier(Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|string|max:255|unique:tiers',
            'level' => 'required|string|max:255',
            'measurement_frequency' => 'required|string|max:255',
            'attribution' => 'required|string|max:255',
        ]);

        Tier::create($validated);

        return redirect()->route('programs.tiers.index')
                        ->with('success', 'Tier created successfully');
    }

    public function editTier(Tier $tier)
    {
        return Inertia::render('Programs/Tiers/Edit', [
            'tier' => $tier
        ]);
    }

    public function updateTier(Request $request, Tier $tier)
    {
        $validated = $request->validate([
            'tier' => 'required|string|max:255|unique:tiers,tier,' . $tier->id,
            'level' => 'required|string|max:255',
            'measurement_frequency' => 'required|string|max:255',
            'attribution' => 'required|string|max:255',
        ]);

        $tier->update($validated);

        return redirect()->route('programs.tiers.index')
                        ->with('success', 'Tier updated successfully');
    }

    public function destroyTier(Tier $tier)
    {
        $tier->delete();
        return redirect()->route('programs.tiers.index')
                        ->with('success', 'Tier deleted successfully');
    }

    // ==================== Cross-Cutting Metrics ====================
    public function crossCuttingMetrics(Request $request)
    {
        $query = CrossCuttingMetric::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('area', 'like', "%{$search}%")
                  ->orWhere('key_metric', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $metrics = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                        ->paginate($request->per_page ?? 10);

        return Inertia::render('Programs/CrossCuttingMetrics/Index', [
            'metrics' => $metrics,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => CrossCuttingMetric::count()
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
            'metric' => $crossCuttingMetric
        ]);
    }

    public function updateCrossCuttingMetric(Request $request, CrossCuttingMetric $crossCuttingMetric)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:cross_cutting_metrics,code,' . $crossCuttingMetric->id,
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
                            ->paginate($request->per_page ?? 15);

        return Inertia::render('Programs/Disagregations/Index', [
            'categories' => $categories,
            'filters'    => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
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
            'name'  => 'required|string|max:255|unique:disagregation_categories,name',
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
            'name' => 'required|string|max:255|unique:disagregation_categories,name,' . $category->id,
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
        $query = IndicatorBaselineYear::with('indicator:id,code,title')
            ->when($request->indicator_id, fn($q) => $q->where('indicator_id', $request->indicator_id))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('indicator', fn($i) =>
                    $i->where('code', 'like', "%{$request->search}%")
                      ->orWhere('title', 'like', "%{$request->search}%")
                );
            });

        $baselines = $query
            ->orderBy($request->sort_by ?? 'baseline_year', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 15);

        return Inertia::render('Programs/Baselines/Index', [
            'baselines'   => $baselines,
            'indicators'  => Indicator::orderBy('code')->get(['id', 'code', 'title']),
            'filters'     => $request->only(['search', 'indicator_id', 'per_page', 'sort_by', 'sort_order']),
            'totalCount'  => IndicatorBaselineYear::count(),
        ]);
    }

    public function createBaseline()
    {
        return Inertia::render('Programs/Baselines/Create', [
            'indicators' => Indicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function storeBaseline(Request $request)
    {
        $request->validate([
            'indicator_id'  => 'required|exists:indicators,id',
            'baseline_year' => 'nullable|integer|min:1900|max:2100',
            'target_year'   => 'nullable|integer|min:1900|max:2100',
            'baseline'      => 'required|numeric',
            'target'        => 'required|numeric',
            'actual'        => 'required|numeric',
        ]);

        IndicatorBaselineYear::create($request->only([
            'indicator_id', 'baseline_year', 'target_year', 'baseline', 'target', 'actual',
        ]));

        return redirect()->route('programs.baselines.index')
                        ->with('success', 'Baseline year added successfully');
    }

    public function editBaseline(IndicatorBaselineYear $baseline)
    {
        return Inertia::render('Programs/Baselines/Edit', [
            'baseline'   => $baseline->load('indicator:id,code,title'),
            'indicators' => Indicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function updateBaseline(Request $request, IndicatorBaselineYear $baseline)
    {
        $request->validate([
            'indicator_id'  => 'required|exists:indicators,id',
            'baseline_year' => 'nullable|integer|min:1900|max:2100',
            'target_year'   => 'nullable|integer|min:1900|max:2100',
            'baseline'      => 'required|numeric',
            'target'        => 'required|numeric',
            'actual'        => 'required|numeric',
        ]);

        $baseline->update($request->only([
            'indicator_id', 'baseline_year', 'target_year', 'baseline', 'target', 'actual',
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
