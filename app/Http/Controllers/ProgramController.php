<?php

namespace App\Http\Controllers;

use App\Models\PresidentialPriority;
use App\Models\SectoralGoal;
use App\Models\BondOutcome;
use App\Models\NlgasPillar;
use App\Models\StrategicAlignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    // Presidential Priorities
    public function presidentialPriorities(Request $request)
    {
        $query = PresidentialPriority::query()->with(['sectoralGoals', 'bondOutcomes', 'nlgasPillars']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
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
            'sectoralGoals' => SectoralGoal::all(),
            'bondOutcomes' => BondOutcome::all(),
            'nlgasPillars' => NlgasPillar::all()
        ]);
    }

    public function storePresidentialPriority(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:presidential_priorities',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'baseline_year' => 'nullable|integer',
            'target_year' => 'nullable|integer',
            'source_document' => 'nullable|string',
            'sectoral_goal_ids' => 'nullable|array',
            'bond_outcome_ids' => 'nullable|array',
            'nlgas_pillar_ids' => 'nullable|array',
        ]);

        $priority = PresidentialPriority::create($validated);

        if ($request->has('sectoral_goal_ids')) {
            $priority->sectoralGoals()->attach($request->sectoral_goal_ids);
        }
        if ($request->has('bond_outcome_ids')) {
            $priority->bondOutcomes()->attach($request->bond_outcome_ids);
        }
        if ($request->has('nlgas_pillar_ids')) {
            $priority->nlgasPillars()->attach($request->nlgas_pillar_ids);
        }

        return redirect()->route('programs.presidential-priorities.index')
                        ->with('success', 'Presidential Priority created successfully');
    }

    public function editPresidentialPriority(PresidentialPriority $priority)
    {
        return Inertia::render('Programs/PresidentialPriorities/Edit', [
            'priority' => $priority->load(['sectoralGoals', 'bondOutcomes', 'nlgasPillars']),
            'sectoralGoals' => SectoralGoal::all(),
            'bondOutcomes' => BondOutcome::all(),
            'nlgasPillars' => NlgasPillar::all()
        ]);
    }

    public function updatePresidentialPriority(Request $request, PresidentialPriority $priority)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:presidential_priorities,code,' . $priority->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'baseline_year' => 'nullable|integer',
            'target_year' => 'nullable|integer',
            'source_document' => 'nullable|string',
            'sectoral_goal_ids' => 'nullable|array',
            'bond_outcome_ids' => 'nullable|array',
            'nlgas_pillar_ids' => 'nullable|array',
        ]);

        $priority->update($validated);

        $priority->sectoralGoals()->sync($request->sectoral_goal_ids ?? []);
        $priority->bondOutcomes()->sync($request->bond_outcome_ids ?? []);
        $priority->nlgasPillars()->sync($request->nlgas_pillar_ids ?? []);

        return redirect()->route('programs.presidential-priorities.index')
                        ->with('success', 'Presidential Priority updated successfully');
    }

    public function destroyPresidentialPriority(PresidentialPriority $priority)
    {
        $priority->delete();
        return redirect()->route('programs.presidential-priorities.index')
                        ->with('success', 'Presidential Priority deleted successfully');
    }

    // Sectoral Goals
    public function sectoralGoals(Request $request)
    {
        $query = SectoralGoal::query()->with(['presidentialPriorities', 'bondOutcomes', 'nlgasPillars']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
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
            'presidentialPriorities' => PresidentialPriority::all(),
            'bondOutcomes' => BondOutcome::all(),
            'nlgasPillars' => NlgasPillar::all()
        ]);
    }

    public function storeSectoralGoal(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:sectoral_goals',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'baseline_year' => 'nullable|integer',
            'target_year' => 'nullable|integer',
            'source_document' => 'nullable|string',
            'responsible_entity' => 'nullable|string',
            'presidential_priority_ids' => 'nullable|array',
            'bond_outcome_ids' => 'nullable|array',
            'nlgas_pillar_ids' => 'nullable|array',
        ]);

        $goal = SectoralGoal::create($validated);

        if ($request->has('presidential_priority_ids')) {
            $goal->presidentialPriorities()->attach($request->presidential_priority_ids);
        }
        if ($request->has('bond_outcome_ids')) {
            $goal->bondOutcomes()->attach($request->bond_outcome_ids);
        }
        if ($request->has('nlgas_pillar_ids')) {
            $goal->nlgasPillars()->attach($request->nlgas_pillar_ids);
        }

        return redirect()->route('programs.sectoral-goals.index')
                        ->with('success', 'Sectoral Goal created successfully');
    }

    public function editSectoralGoal(SectoralGoal $goal)
    {
        return Inertia::render('Programs/SectoralGoals/Edit', [
            'goal' => $goal->load(['presidentialPriorities', 'bondOutcomes', 'nlgasPillars']),
            'presidentialPriorities' => PresidentialPriority::all(),
            'bondOutcomes' => BondOutcome::all(),
            'nlgasPillars' => NlgasPillar::all()
        ]);
    }

    public function updateSectoralGoal(Request $request, SectoralGoal $goal)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:sectoral_goals,code,' . $goal->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'baseline_year' => 'nullable|integer',
            'target_year' => 'nullable|integer',
            'source_document' => 'nullable|string',
            'responsible_entity' => 'nullable|string',
            'presidential_priority_ids' => 'nullable|array',
            'bond_outcome_ids' => 'nullable|array',
            'nlgas_pillar_ids' => 'nullable|array',
        ]);

        $goal->update($validated);

        $goal->presidentialPriorities()->sync($request->presidential_priority_ids ?? []);
        $goal->bondOutcomes()->sync($request->bond_outcome_ids ?? []);
        $goal->nlgasPillars()->sync($request->nlgas_pillar_ids ?? []);

        return redirect()->route('programs.sectoral-goals.index')
                        ->with('success', 'Sectoral Goal updated successfully');
    }

    public function destroySectoralGoal(SectoralGoal $goal)
    {
        $goal->delete();
        return redirect()->route('programs.sectoral-goals.index')
                        ->with('success', 'Sectoral Goal deleted successfully');
    }

    // Bond Outcomes
    public function bondOutcomes(Request $request)
    {
        $query = BondOutcome::query()->with(['presidentialPriorities', 'sectoralGoals', 'nlgasPillars']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
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
            'presidentialPriorities' => PresidentialPriority::all(),
            'sectoralGoals' => SectoralGoal::all(),
            'nlgasPillars' => NlgasPillar::all()
        ]);
    }

    public function storeBondOutcome(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:bond_outcomes',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'baseline_year' => 'nullable|integer',
            'target_year' => 'nullable|integer',
            'source_document' => 'nullable|string',
            'responsible_entity' => 'nullable|string',
            'presidential_priority_ids' => 'nullable|array',
            'sectoral_goal_ids' => 'nullable|array',
            'nlgas_pillar_ids' => 'nullable|array',
        ]);

        $outcome = BondOutcome::create($validated);

        if ($request->has('presidential_priority_ids')) {
            $outcome->presidentialPriorities()->attach($request->presidential_priority_ids);
        }
        if ($request->has('sectoral_goal_ids')) {
            $outcome->sectoralGoals()->attach($request->sectoral_goal_ids);
        }
        if ($request->has('nlgas_pillar_ids')) {
            $outcome->nlgasPillars()->attach($request->nlgas_pillar_ids);
        }

        return redirect()->route('programs.bond-outcomes.index')
                        ->with('success', 'Bond Outcome created successfully');
    }

    public function editBondOutcome(BondOutcome $outcome)
    {
        return Inertia::render('Programs/BondOutcomes/Edit', [
            'outcome' => $outcome->load(['presidentialPriorities', 'sectoralGoals', 'nlgasPillars']),
            'presidentialPriorities' => PresidentialPriority::all(),
            'sectoralGoals' => SectoralGoal::all(),
            'nlgasPillars' => NlgasPillar::all()
        ]);
    }

    public function updateBondOutcome(Request $request, BondOutcome $outcome)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:bond_outcomes,code,' . $outcome->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'baseline_year' => 'nullable|integer',
            'target_year' => 'nullable|integer',
            'source_document' => 'nullable|string',
            'responsible_entity' => 'nullable|string',
            'presidential_priority_ids' => 'nullable|array',
            'sectoral_goal_ids' => 'nullable|array',
            'nlgas_pillar_ids' => 'nullable|array',
        ]);

        $outcome->update($validated);

        $outcome->presidentialPriorities()->sync($request->presidential_priority_ids ?? []);
        $outcome->sectoralGoals()->sync($request->sectoral_goal_ids ?? []);
        $outcome->nlgasPillars()->sync($request->nlgas_pillar_ids ?? []);

        return redirect()->route('programs.bond-outcomes.index')
                        ->with('success', 'Bond Outcome updated successfully');
    }

    public function destroyBondOutcome(BondOutcome $outcome)
    {
        $outcome->delete();
        return redirect()->route('programs.bond-outcomes.index')
                        ->with('success', 'Bond Outcome deleted successfully');
    }

    // NLGAS Pillars
    public function nlgasPillars(Request $request)
    {
        $query = NlgasPillar::query()->with(['presidentialPriorities', 'sectoralGoals', 'bondOutcomes']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
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
            'presidentialPriorities' => PresidentialPriority::all(),
            'sectoralGoals' => SectoralGoal::all(),
            'bondOutcomes' => BondOutcome::all()
        ]);
    }

    public function storeNlgasPillar(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:nlgas_pillars',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'baseline_year' => 'nullable|integer',
            'target_year' => 'nullable|integer',
            'source_document' => 'nullable|string',
            'responsible_entity' => 'nullable|string',
            'presidential_priority_ids' => 'nullable|array',
            'sectoral_goal_ids' => 'nullable|array',
            'bond_outcome_ids' => 'nullable|array',
        ]);

        $pillar = NlgasPillar::create($validated);

        if ($request->has('presidential_priority_ids')) {
            $pillar->presidentialPriorities()->attach($request->presidential_priority_ids);
        }
        if ($request->has('sectoral_goal_ids')) {
            $pillar->sectoralGoals()->attach($request->sectoral_goal_ids);
        }
        if ($request->has('bond_outcome_ids')) {
            $pillar->bondOutcomes()->attach($request->bond_outcome_ids);
        }

        return redirect()->route('programs.nlgas-pillars.index')
                        ->with('success', 'NLGAS Pillar created successfully');
    }

    public function editNlgasPillar(NlgasPillar $pillar)
    {
        return Inertia::render('Programs/NlgasPillars/Edit', [
            'pillar' => $pillar->load(['presidentialPriorities', 'sectoralGoals', 'bondOutcomes']),
            'presidentialPriorities' => PresidentialPriority::all(),
            'sectoralGoals' => SectoralGoal::all(),
            'bondOutcomes' => BondOutcome::all()
        ]);
    }

    public function updateNlgasPillar(Request $request, NlgasPillar $pillar)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:nlgas_pillars,code,' . $pillar->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'baseline_year' => 'nullable|integer',
            'target_year' => 'nullable|integer',
            'source_document' => 'nullable|string',
            'responsible_entity' => 'nullable|string',
            'presidential_priority_ids' => 'nullable|array',
            'sectoral_goal_ids' => 'nullable|array',
            'bond_outcome_ids' => 'nullable|array',
        ]);

        $pillar->update($validated);

        $pillar->presidentialPriorities()->sync($request->presidential_priority_ids ?? []);
        $pillar->sectoralGoals()->sync($request->sectoral_goal_ids ?? []);
        $pillar->bondOutcomes()->sync($request->bond_outcome_ids ?? []);

        return redirect()->route('programs.nlgas-pillars.index')
                        ->with('success', 'NLGAS Pillar updated successfully');
    }

    public function destroyNlgasPillar(NlgasPillar $pillar)
    {
        $pillar->delete();
        return redirect()->route('programs.nlgas-pillars.index')
                        ->with('success', 'NLGAS Pillar deleted successfully');
    }
}
