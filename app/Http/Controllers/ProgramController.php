<?php

namespace App\Http\Controllers;

use App\Models\PresidentialPriority;
use App\Models\SectoralGoal;
use App\Models\BondOutcome;
use App\Models\NlgasPillar;
use App\Models\Program;
use App\Models\Tier;
use App\Models\Indicator;
use App\Models\CrossCuttingMetric;
use App\Models\Department;
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

    // ==================== Bond Outcomes ====================
    public function bondOutcomes(Request $request)
    {
        $query = BondOutcome::query()->with(['tiers']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $outcomes = $query->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                         ->paginate($request->per_page ?? 10);

        return Inertia::render('Programs/BondOutcomes/Index', [
            'outcomes' => $outcomes,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => BondOutcome::count()
        ]);
    }

    public function createBondOutcome()
    {
        return Inertia::render('Programs/BondOutcomes/Create', [
            'tiers' => Tier::all()
        ]);
    }

    public function storeBondOutcome(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:bond_outcomes',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $outcome = BondOutcome::create($validated);

        if ($request->has('tier_ids')) {
            $outcome->tiers()->attach($request->tier_ids);
        }

        return redirect()->route('programs.bond-outcomes.index')
                        ->with('success', 'Bond Outcome created successfully');
    }

    public function editBondOutcome(BondOutcome $outcome)
    {
        return Inertia::render('Programs/BondOutcomes/Edit', [
            'outcome' => $outcome->load(['tiers']),
            'tiers' => Tier::all()
        ]);
    }

    public function updateBondOutcome(Request $request, BondOutcome $outcome)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:bond_outcomes,code,' . $outcome->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $outcome->update($validated);
        $outcome->tiers()->sync($request->tier_ids ?? []);

        return redirect()->route('programs.bond-outcomes.index')
                        ->with('success', 'Bond Outcome updated successfully');
    }

    public function destroyBondOutcome(BondOutcome $outcome)
    {
        $outcome->delete();
        return redirect()->route('programs.bond-outcomes.index')
                        ->with('success', 'Bond Outcome deleted successfully');
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
        $query = Indicator::query()->with(['tiers']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('indicator_type')) {
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
            'tiers' => Tier::all()
        ]);
    }

    public function storeIndicator(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:indicators',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'indicator_type' => 'required|in:outcome,output,impact',
            'measurement_unit' => 'nullable|string',
            'baseline_value' => 'nullable|numeric',
            'baseline_year' => 'nullable|integer',
            'target_value' => 'nullable|numeric',
            'target_year' => 'nullable|integer',
            'data_source' => 'nullable|string',
            'collection_frequency' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $indicator = Indicator::create($validated);

        if ($request->has('tier_ids')) {
            $indicator->tiers()->attach($request->tier_ids);
        }

        return redirect()->route('programs.indicators.index')
                        ->with('success', 'Indicator created successfully');
    }

    public function editIndicator(Indicator $indicator)
    {
        return Inertia::render('Programs/Indicators/Edit', [
            'indicator' => $indicator->load(['tiers']),
            'tiers' => Tier::all()
        ]);
    }

    public function updateIndicator(Request $request, Indicator $indicator)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:indicators,code,' . $indicator->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'indicator_type' => 'required|in:outcome,output,impact',
            'measurement_unit' => 'nullable|string',
            'baseline_value' => 'nullable|numeric',
            'baseline_year' => 'nullable|integer',
            'target_value' => 'nullable|numeric',
            'target_year' => 'nullable|integer',
            'data_source' => 'nullable|string',
            'collection_frequency' => 'nullable|string',
            'tier_ids' => 'nullable|array',
        ]);

        $indicator->update($validated);
        $indicator->tiers()->sync($request->tier_ids ?? []);

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
            ->withCount(['indicators', 'sectoralGoals', 'presidentialPriorities', 'bondOutcomes']);

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
}
