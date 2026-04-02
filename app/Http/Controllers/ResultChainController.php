<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\BondOutputIndicator;
use App\Models\Department;
use App\Models\DisagregationCategory;
use App\Models\DisagregationItem;
use App\Models\ImpactIndicator;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use App\Models\PillarProgramOutputIndicator;
use App\Models\PresidentialPriority;
use App\Models\Program;
use App\Models\SectoralGoal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResultChainController extends Controller
{
    // ─────────────────────── INPUTS ────────────────────────────

    public function inputs()
    {
        return Inertia::render('ResultChain/Inputs/Index');
    }

    // ─────────────────────── ACTIVITIES ────────────────────────

    public function activities(Request $request)
    {
        $query = Activity::with('program')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            }))
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id));

        $sortBy    = in_array($request->sort_by, ['code', 'title', 'created_at']) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        return Inertia::render('ResultChain/Activities/Activities/Index', [
            'activities' => $query->orderBy($sortBy, $sortOrder)->paginate($request->per_page ?? 15)->withQueryString(),
            'programs'   => Program::orderBy('code')->get(['id', 'code', 'title']),
            'filters'    => $request->only(['search', 'per_page', 'sort_by', 'sort_order', 'program_id']),
            'totalCount' => Activity::count(),
        ]);
    }

    public function createActivity()
    {
        return Inertia::render('ResultChain/Activities/Activities/Create', [
            'programs'         => Program::orderBy('code')->get(['id', 'code', 'title']),
            'outputIndicators' => OutputIndicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function storeActivity(Request $request)
    {
        $data = $request->validate([
            'program_id'             => 'required|exists:programs,id',
            'code'                   => 'required|string|max:50|unique:activities,code',
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'output_indicator_ids'   => 'nullable|array',
            'output_indicator_ids.*' => 'exists:output_indicators,id',
        ]);

        $activity = Activity::create([
            'program_id'  => $data['program_id'],
            'code'        => $data['code'],
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        if (!empty($data['output_indicator_ids'])) {
            $activity->outputIndicators()->sync($data['output_indicator_ids']);
        }

        return redirect()->route('result-chain.activities.index')
            ->with('success', 'Activity created successfully.');
    }

    public function editActivity(Activity $activity)
    {
        $activity->load('outputIndicators:id');

        return Inertia::render('ResultChain/Activities/Activities/Edit', [
            'activity'         => $activity,
            'selectedIds'      => $activity->outputIndicators->pluck('id')->toArray(),
            'programs'         => Program::orderBy('code')->get(['id', 'code', 'title']),
            'outputIndicators' => OutputIndicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function updateActivity(Request $request, Activity $activity)
    {
        $data = $request->validate([
            'program_id'             => 'required|exists:programs,id',
            'code'                   => 'required|string|max:50|unique:activities,code,' . $activity->id,
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'output_indicator_ids'   => 'nullable|array',
            'output_indicator_ids.*' => 'exists:output_indicators,id',
        ]);

        $activity->update([
            'program_id'  => $data['program_id'],
            'code'        => $data['code'],
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        $activity->outputIndicators()->sync($data['output_indicator_ids'] ?? []);

        return redirect()->route('result-chain.activities.index')
            ->with('success', 'Activity updated successfully.');
    }

    public function destroyActivity(Activity $activity)
    {
        $activity->outputIndicators()->detach();
        $activity->delete();

        return redirect()->route('result-chain.activities.index')
            ->with('success', 'Activity deleted successfully.');
    }

    // ─────────────────────── OUTPUT INDICATORS ──────────────────

    public function outputIndicators(Request $request)
    {
        $query = OutputIndicator::with('mainDepartment:id,name')
            ->withCount(['activities', 'outcomeIndicators'])
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            }));

        $sortBy    = in_array($request->sort_by, ['code', 'title', 'created_at']) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        return Inertia::render('ResultChain/Outputs/OutputIndicators/Index', [
            'indicators' => $query->orderBy($sortBy, $sortOrder)->paginate($request->per_page ?? 15)->withQueryString(),
            'filters'    => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => OutputIndicator::count(),
        ]);
    }

    public function createOutputIndicator()
    {
        return Inertia::render('ResultChain/Outputs/OutputIndicators/Create', [
            'departments'             => Department::orderBy('name')->get(['id', 'name']),
            'disagregationCategories' => DisagregationCategory::with('items')->orderBy('name')->get(),
            'outcomeIndicators'       => OutcomeIndicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function storeOutputIndicator(Request $request)
    {
        $data = $request->validate([
            'title'                          => 'required|string|max:255',
            'description'                    => 'nullable|string',
            'department_id'                  => 'required|exists:departments,id',
            'supporting_department_ids'      => 'nullable|array',
            'supporting_department_ids.*'    => 'exists:departments,id',
            'measurement_unit'               => 'nullable|string|max:100',
            'disagregation_item_ids'         => 'nullable|array',
            'disagregation_item_ids.*'       => 'exists:disagregation_items,id',
            'outcome_indicator_ids'          => 'required|array|min:1',
            'outcome_indicator_ids.*'        => 'exists:outcome_indicators,id',
        ]);

        $indicator = OutputIndicator::create([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'department_id'    => $data['department_id'],
            'measurement_unit' => $data['measurement_unit'] ?? null,
        ]);

        $indicator->outcomeIndicators()->sync($data['outcome_indicator_ids']);
        $indicator->disagregationItems()->sync($data['disagregation_item_ids'] ?? []);
        $indicator->supportingDepartments()->sync($data['supporting_department_ids'] ?? []);

        return redirect()->route('result-chain.output-indicators.index')
            ->with('success', 'Output indicator created successfully.');
    }

    public function editOutputIndicator(OutputIndicator $outputIndicator)
    {
        $outputIndicator->load(['outcomeIndicators:id', 'disagregationItems:id', 'supportingDepartments:id']);

        return Inertia::render('ResultChain/Outputs/OutputIndicators/Edit', [
            'indicator'               => $outputIndicator,
            'selectedOutcomes'        => $outputIndicator->outcomeIndicators->pluck('id')->toArray(),
            'selectedDisagreg'        => $outputIndicator->disagregationItems->pluck('id')->toArray(),
            'selectedSupportingDept'  => $outputIndicator->supportingDepartments->pluck('id')->toArray(),
            'departments'             => Department::orderBy('name')->get(['id', 'name']),
            'disagregationCategories' => DisagregationCategory::with('items')->orderBy('name')->get(),
            'outcomeIndicators'       => OutcomeIndicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function updateOutputIndicator(Request $request, OutputIndicator $outputIndicator)
    {
        $data = $request->validate([
            'title'                          => 'required|string|max:255',
            'description'                    => 'nullable|string',
            'department_id'                  => 'required|exists:departments,id',
            'supporting_department_ids'      => 'nullable|array',
            'supporting_department_ids.*'    => 'exists:departments,id',
            'measurement_unit'               => 'nullable|string|max:100',
            'disagregation_item_ids'         => 'nullable|array',
            'disagregation_item_ids.*'       => 'exists:disagregation_items,id',
            'outcome_indicator_ids'          => 'required|array|min:1',
            'outcome_indicator_ids.*'        => 'exists:outcome_indicators,id',
        ]);

        $outputIndicator->update([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'department_id'    => $data['department_id'],
            'measurement_unit' => $data['measurement_unit'] ?? null,
        ]);

        $outputIndicator->outcomeIndicators()->sync($data['outcome_indicator_ids']);
        $outputIndicator->disagregationItems()->sync($data['disagregation_item_ids'] ?? []);
        $outputIndicator->supportingDepartments()->sync($data['supporting_department_ids'] ?? []);

        return redirect()->route('result-chain.output-indicators.index')
            ->with('success', 'Output indicator updated successfully.');
    }

    public function destroyOutputIndicator(OutputIndicator $outputIndicator)
    {
        $outputIndicator->outcomeIndicators()->detach();
        $outputIndicator->disagregationItems()->detach();
        $outputIndicator->activities()->detach();
        $outputIndicator->supportingDepartments()->detach();
        $outputIndicator->delete();

        return redirect()->route('result-chain.output-indicators.index')
            ->with('success', 'Output indicator deleted successfully.');
    }

    // ─────────────────────── OUTCOME INDICATORS ─────────────────

    public function outcomeIndicators(Request $request)
    {
        $query = OutcomeIndicator::with('mainDepartment:id,name')
            ->withCount(['impactIndicators', 'outputIndicators'])
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            }));

        $sortBy    = in_array($request->sort_by, ['code', 'title', 'created_at']) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        return Inertia::render('ResultChain/Outcomes/OutcomeIndicators/Index', [
            'indicators' => $query->orderBy($sortBy, $sortOrder)->paginate($request->per_page ?? 15)->withQueryString(),
            'filters'    => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => OutcomeIndicator::count(),
        ]);
    }

    public function createOutcomeIndicator()
    {
        return Inertia::render('ResultChain/Outcomes/OutcomeIndicators/Create', [
            'departments'             => Department::orderBy('name')->get(['id', 'name']),
            'disagregationCategories' => DisagregationCategory::with('items')->orderBy('name')->get(),
            'impactIndicators'        => ImpactIndicator::orderBy('code')->get(['id', 'code', 'title']),
            'sectoralGoals'           => SectoralGoal::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function storeOutcomeIndicator(Request $request)
    {
        $data = $request->validate([
            'title'                          => 'required|string|max:255',
            'description'                    => 'nullable|string',
            'department_id'                  => 'required|exists:departments,id',
            'supporting_department_ids'      => 'nullable|array',
            'supporting_department_ids.*'    => 'exists:departments,id',
            'measurement_unit'               => 'nullable|string|max:100',
            'disagregation_item_ids'         => 'nullable|array',
            'disagregation_item_ids.*'       => 'exists:disagregation_items,id',
            'impact_indicator_ids'           => 'required|array|min:1',
            'impact_indicator_ids.*'         => 'exists:impact_indicators,id',
            'sectoral_goal_ids'              => 'nullable|array',
            'sectoral_goal_ids.*'            => 'exists:sectoral_goals,id',
        ]);

        $indicator = OutcomeIndicator::create([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'department_id'    => $data['department_id'],
            'measurement_unit' => $data['measurement_unit'] ?? null,
        ]);

        $indicator->impactIndicators()->sync($data['impact_indicator_ids']);
        $indicator->disagregationItems()->sync($data['disagregation_item_ids'] ?? []);
        $indicator->sectoralGoals()->sync($data['sectoral_goal_ids'] ?? []);
        $indicator->supportingDepartments()->sync($data['supporting_department_ids'] ?? []);

        return redirect()->route('result-chain.outcome-indicators.index')
            ->with('success', 'Outcome indicator created successfully.');
    }

    public function editOutcomeIndicator(OutcomeIndicator $outcomeIndicator)
    {
        $outcomeIndicator->load(['impactIndicators:id', 'disagregationItems:id', 'sectoralGoals:id', 'supportingDepartments:id']);

        return Inertia::render('ResultChain/Outcomes/OutcomeIndicators/Edit', [
            'indicator'               => $outcomeIndicator,
            'selectedImpacts'         => $outcomeIndicator->impactIndicators->pluck('id')->toArray(),
            'selectedDisagreg'        => $outcomeIndicator->disagregationItems->pluck('id')->toArray(),
            'selectedGoals'           => $outcomeIndicator->sectoralGoals->pluck('id')->toArray(),
            'selectedSupportingDept'  => $outcomeIndicator->supportingDepartments->pluck('id')->toArray(),
            'departments'             => Department::orderBy('name')->get(['id', 'name']),
            'disagregationCategories' => DisagregationCategory::with('items')->orderBy('name')->get(),
            'impactIndicators'        => ImpactIndicator::orderBy('code')->get(['id', 'code', 'title']),
            'sectoralGoals'           => SectoralGoal::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function updateOutcomeIndicator(Request $request, OutcomeIndicator $outcomeIndicator)
    {
        $data = $request->validate([
            'title'                          => 'required|string|max:255',
            'description'                    => 'nullable|string',
            'department_id'                  => 'required|exists:departments,id',
            'supporting_department_ids'      => 'nullable|array',
            'supporting_department_ids.*'    => 'exists:departments,id',
            'measurement_unit'               => 'nullable|string|max:100',
            'disagregation_item_ids'         => 'nullable|array',
            'disagregation_item_ids.*'       => 'exists:disagregation_items,id',
            'impact_indicator_ids'           => 'required|array|min:1',
            'impact_indicator_ids.*'         => 'exists:impact_indicators,id',
            'sectoral_goal_ids'              => 'nullable|array',
            'sectoral_goal_ids.*'            => 'exists:sectoral_goals,id',
        ]);

        $outcomeIndicator->update([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'department_id'    => $data['department_id'],
            'measurement_unit' => $data['measurement_unit'] ?? null,
        ]);

        $outcomeIndicator->impactIndicators()->sync($data['impact_indicator_ids']);
        $outcomeIndicator->disagregationItems()->sync($data['disagregation_item_ids'] ?? []);
        $outcomeIndicator->sectoralGoals()->sync($data['sectoral_goal_ids'] ?? []);
        $outcomeIndicator->supportingDepartments()->sync($data['supporting_department_ids'] ?? []);

        return redirect()->route('result-chain.outcome-indicators.index')
            ->with('success', 'Outcome indicator updated successfully.');
    }

    public function destroyOutcomeIndicator(OutcomeIndicator $outcomeIndicator)
    {
        $outcomeIndicator->impactIndicators()->detach();
        $outcomeIndicator->disagregationItems()->detach();
        $outcomeIndicator->sectoralGoals()->detach();
        $outcomeIndicator->supportingDepartments()->detach();
        $outcomeIndicator->delete();

        return redirect()->route('result-chain.outcome-indicators.index')
            ->with('success', 'Outcome indicator deleted successfully.');
    }

    // ─────────────────────── IMPACT INDICATORS ──────────────────

    public function impactIndicators(Request $request)
    {
        $query = ImpactIndicator::with('mainDepartment:id,name')
            ->withCount(['outcomeIndicators', 'presidentialPriorities'])
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            }));

        $sortBy    = in_array($request->sort_by, ['code', 'title', 'created_at']) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        return Inertia::render('ResultChain/Impacts/ImpactIndicators/Index', [
            'indicators' => $query->orderBy($sortBy, $sortOrder)->paginate($request->per_page ?? 15)->withQueryString(),
            'filters'    => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => ImpactIndicator::count(),
        ]);
    }

    public function createImpactIndicator()
    {
        return Inertia::render('ResultChain/Impacts/ImpactIndicators/Create', [
            'departments'             => Department::orderBy('name')->get(['id', 'name']),
            'disagregationCategories' => DisagregationCategory::with('items')->orderBy('name')->get(),
            'presidentialPriorities'  => PresidentialPriority::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function storeImpactIndicator(Request $request)
    {
        $data = $request->validate([
            'title'                          => 'required|string|max:255',
            'description'                    => 'nullable|string',
            'department_id'                  => 'required|exists:departments,id',
            'supporting_department_ids'      => 'nullable|array',
            'supporting_department_ids.*'    => 'exists:departments,id',
            'measurement_unit'               => 'nullable|string|max:100',
            'disagregation_item_ids'         => 'nullable|array',
            'disagregation_item_ids.*'       => 'exists:disagregation_items,id',
            'presidential_priority_ids'      => 'nullable|array',
            'presidential_priority_ids.*'    => 'exists:presidential_priorities,id',
        ]);

        $indicator = ImpactIndicator::create([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'department_id'    => $data['department_id'],
            'measurement_unit' => $data['measurement_unit'] ?? null,
        ]);

        $indicator->presidentialPriorities()->sync($data['presidential_priority_ids'] ?? []);
        $indicator->disagregationItems()->sync($data['disagregation_item_ids'] ?? []);
        $indicator->supportingDepartments()->sync($data['supporting_department_ids'] ?? []);

        return redirect()->route('result-chain.impact-indicators.index')
            ->with('success', 'Impact indicator created successfully.');
    }

    public function editImpactIndicator(ImpactIndicator $impactIndicator)
    {
        $impactIndicator->load(['presidentialPriorities:id', 'disagregationItems:id', 'supportingDepartments:id']);

        return Inertia::render('ResultChain/Impacts/ImpactIndicators/Edit', [
            'indicator'               => $impactIndicator,
            'selectedPriorities'      => $impactIndicator->presidentialPriorities->pluck('id')->toArray(),
            'selectedDisagreg'        => $impactIndicator->disagregationItems->pluck('id')->toArray(),
            'selectedSupportingDept'  => $impactIndicator->supportingDepartments->pluck('id')->toArray(),
            'departments'             => Department::orderBy('name')->get(['id', 'name']),
            'disagregationCategories' => DisagregationCategory::with('items')->orderBy('name')->get(),
            'presidentialPriorities'  => PresidentialPriority::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function updateImpactIndicator(Request $request, ImpactIndicator $impactIndicator)
    {
        $data = $request->validate([
            'title'                          => 'required|string|max:255',
            'description'                    => 'nullable|string',
            'department_id'                  => 'required|exists:departments,id',
            'supporting_department_ids'      => 'nullable|array',
            'supporting_department_ids.*'    => 'exists:departments,id',
            'measurement_unit'               => 'nullable|string|max:100',
            'disagregation_item_ids'         => 'nullable|array',
            'disagregation_item_ids.*'       => 'exists:disagregation_items,id',
            'presidential_priority_ids'      => 'nullable|array',
            'presidential_priority_ids.*'    => 'exists:presidential_priorities,id',
        ]);

        $impactIndicator->update([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'department_id'    => $data['department_id'],
            'measurement_unit' => $data['measurement_unit'] ?? null,
        ]);

        $impactIndicator->presidentialPriorities()->sync($data['presidential_priority_ids'] ?? []);
        $impactIndicator->disagregationItems()->sync($data['disagregation_item_ids'] ?? []);
        $impactIndicator->supportingDepartments()->sync($data['supporting_department_ids'] ?? []);

        return redirect()->route('result-chain.impact-indicators.index')
            ->with('success', 'Impact indicator updated successfully.');
    }

    public function destroyImpactIndicator(ImpactIndicator $impactIndicator)
    {
        $impactIndicator->presidentialPriorities()->detach();
        $impactIndicator->disagregationItems()->detach();
        $impactIndicator->outcomeIndicators()->detach();
        $impactIndicator->supportingDepartments()->detach();
        $impactIndicator->delete();

        return redirect()->route('result-chain.impact-indicators.index')
            ->with('success', 'Impact indicator deleted successfully.');
    }

    // ─────────────────────── BOND OUTPUT INDICATORS ─────────────

    public function bondOutputIndicators(Request $request)
    {
        $query = BondOutputIndicator::withCount('outcomeIndicators')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            }));

        $sortBy    = in_array($request->sort_by, ['code', 'title', 'created_at']) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        return Inertia::render('ResultChain/Outputs/BondOutputIndicators/Index', [
            'indicators' => $query->orderBy($sortBy, $sortOrder)->paginate($request->per_page ?? 15)->withQueryString(),
            'filters'    => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => BondOutputIndicator::count(),
        ]);
    }

    public function createBondOutputIndicator()
    {
        return Inertia::render('ResultChain/Outputs/BondOutputIndicators/Create', [
            'outcomeIndicators' => OutcomeIndicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function storeBondOutputIndicator(Request $request)
    {
        $data = $request->validate([
            'title'                       => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'outcome_indicator_ids'       => 'nullable|array',
            'outcome_indicator_ids.*'     => 'exists:outcome_indicators,id',
        ]);

        $indicator = BondOutputIndicator::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        $indicator->outcomeIndicators()->sync($data['outcome_indicator_ids'] ?? []);

        return redirect()->route('result-chain.bond-output-indicators.index')
            ->with('success', 'Bond output indicator created successfully.');
    }

    public function editBondOutputIndicator(BondOutputIndicator $bondOutputIndicator)
    {
        $bondOutputIndicator->load('outcomeIndicators:id');

        return Inertia::render('ResultChain/Outputs/BondOutputIndicators/Edit', [
            'indicator'         => $bondOutputIndicator,
            'selectedIds'       => $bondOutputIndicator->outcomeIndicators->pluck('id')->toArray(),
            'outcomeIndicators' => OutcomeIndicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function updateBondOutputIndicator(Request $request, BondOutputIndicator $bondOutputIndicator)
    {
        $data = $request->validate([
            'title'                       => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'outcome_indicator_ids'       => 'nullable|array',
            'outcome_indicator_ids.*'     => 'exists:outcome_indicators,id',
        ]);

        $bondOutputIndicator->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        $bondOutputIndicator->outcomeIndicators()->sync($data['outcome_indicator_ids'] ?? []);

        return redirect()->route('result-chain.bond-output-indicators.index')
            ->with('success', 'Bond output indicator updated successfully.');
    }

    public function destroyBondOutputIndicator(BondOutputIndicator $bondOutputIndicator)
    {
        $bondOutputIndicator->outcomeIndicators()->detach();
        $bondOutputIndicator->delete();

        return redirect()->route('result-chain.bond-output-indicators.index')
            ->with('success', 'Bond output indicator deleted successfully.');
    }

    // ─────────────────────── PILLAR PROGRAM OUTPUT INDICATORS ───

    public function pillarProgramOutputIndicators(Request $request)
    {
        $query = PillarProgramOutputIndicator::with('program:id,code,title')
            ->withCount('outcomeIndicators')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            }));

        $sortBy    = in_array($request->sort_by, ['code', 'title', 'created_at']) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        return Inertia::render('ResultChain/Outputs/ProgramOutputIndicators/Index', [
            'indicators' => $query->orderBy($sortBy, $sortOrder)->paginate($request->per_page ?? 15)->withQueryString(),
            'filters'    => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => PillarProgramOutputIndicator::count(),
        ]);
    }

    public function createPillarProgramOutputIndicator()
    {
        return Inertia::render('ResultChain/Outputs/ProgramOutputIndicators/Create', [
            'programs'          => Program::orderBy('code')->get(['id', 'code', 'title']),
            'outcomeIndicators' => OutcomeIndicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function storePillarProgramOutputIndicator(Request $request)
    {
        $data = $request->validate([
            'title'                       => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'program_id'                  => 'nullable|exists:programs,id',
            'outcome_indicator_ids'       => 'nullable|array',
            'outcome_indicator_ids.*'     => 'exists:outcome_indicators,id',
        ]);

        $indicator = PillarProgramOutputIndicator::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'program_id'  => $data['program_id'] ?? null,
        ]);

        $indicator->outcomeIndicators()->sync($data['outcome_indicator_ids'] ?? []);

        return redirect()->route('result-chain.program-output-indicators.index')
            ->with('success', 'Program output indicator created successfully.');
    }

    public function editPillarProgramOutputIndicator(PillarProgramOutputIndicator $pillarProgramOutputIndicator)
    {
        $pillarProgramOutputIndicator->load('outcomeIndicators:id');

        return Inertia::render('ResultChain/Outputs/ProgramOutputIndicators/Edit', [
            'indicator'         => $pillarProgramOutputIndicator,
            'selectedIds'       => $pillarProgramOutputIndicator->outcomeIndicators->pluck('id')->toArray(),
            'programs'          => Program::orderBy('code')->get(['id', 'code', 'title']),
            'outcomeIndicators' => OutcomeIndicator::orderBy('code')->get(['id', 'code', 'title']),
        ]);
    }

    public function updatePillarProgramOutputIndicator(Request $request, PillarProgramOutputIndicator $pillarProgramOutputIndicator)
    {
        $data = $request->validate([
            'title'                       => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'program_id'                  => 'nullable|exists:programs,id',
            'outcome_indicator_ids'       => 'nullable|array',
            'outcome_indicator_ids.*'     => 'exists:outcome_indicators,id',
        ]);

        $pillarProgramOutputIndicator->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'program_id'  => $data['program_id'] ?? null,
        ]);

        $pillarProgramOutputIndicator->outcomeIndicators()->sync($data['outcome_indicator_ids'] ?? []);

        return redirect()->route('result-chain.program-output-indicators.index')
            ->with('success', 'Program output indicator updated successfully.');
    }

    public function destroyPillarProgramOutputIndicator(PillarProgramOutputIndicator $pillarProgramOutputIndicator)
    {
        $pillarProgramOutputIndicator->outcomeIndicators()->detach();
        $pillarProgramOutputIndicator->delete();

        return redirect()->route('result-chain.program-output-indicators.index')
            ->with('success', 'Program output indicator deleted successfully.');
    }

    // ─────────────────────── DISAGGREGATION QUICK-ADD ───────────

    /**
     * Quick-add a disaggregation item (or category + item) via AJAX.
     * Returns JSON so the Vue component can update its local state without a page reload.
     */
    public function quickAddDisagregationItem(Request $request)
    {
        $data = $request->validate([
            'category_id'   => 'nullable|exists:disagregation_categories,id',
            'category_name' => 'nullable|required_without:category_id|string|max:255',
            'item_name'     => 'required|string|max:255',
        ]);

        if (!empty($data['category_id'])) {
            $category = DisagregationCategory::findOrFail($data['category_id']);
        } else {
            $category = DisagregationCategory::firstOrCreate(['name' => $data['category_name']]);
        }

        $item = $category->items()->create(['name' => $data['item_name']]);

        return response()->json([
            'category' => ['id' => $category->id, 'name' => $category->name],
            'item'     => ['id' => $item->id, 'name' => $item->name, 'disagregation_category_id' => $category->id],
        ]);
    }
}
