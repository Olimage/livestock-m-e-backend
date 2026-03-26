<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\Indicator;

class DepartmentController extends Controller
{
    
    public function getDepartments(Request $request)
    {
        try {
            // Get only top-level departments (parent_id is null) with all descendants
            $departments = Department::whereNull('parent_id')
                ->with('descendants')
                ->get();

            // Recursively hide fields from departments and all descendants
            $departments = $departments->map(function ($department) {
                return $this->hideFieldsRecursively($department);
            });

            return response()->json([
                'status' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== Inertia Management Pages ====================

    public function index(Request $request)
    {
        $query = Department::withCount('indicators');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->type === 'technical') {
            $query->where('is_technical', true);
        } elseif ($request->type === 'non-technical') {
            $query->where('is_technical', false);
        }

        if ($request->agency === 'agency') {
            $query->where('is_agency', true);
        } elseif ($request->agency === 'non-agency') {
            $query->where('is_agency', false);
        }

        $departments = $query
            ->with('parent:id,name')
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        return Inertia::render('Programs/Departments/Index', [
            'departments' => $departments,
            'filters'     => $request->only(['search', 'per_page', 'sort_by', 'sort_order', 'type', 'agency']),
            'totalCount'  => Department::count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Programs/Departments/Create', [
            'parentOptions' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'is_technical' => 'boolean',
            'is_agency'    => 'boolean',
            'parent_id'    => 'nullable|exists:departments,id',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

        Department::create($data);

        return redirect()->route('programs.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return Inertia::render('Programs/Departments/Edit', [
            'department'    => $department,
            'parentOptions' => Department::where('id', '!=', $department->id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'is_technical' => 'boolean',
            'is_agency'    => 'boolean',
            'parent_id'    => 'nullable|exists:departments,id',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

        $department->update($data);

        return redirect()->route('programs.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->children()->exists()) {
            return back()->with('error', 'Cannot delete — department has sub-departments.');
        }

        if ($department->indicators()->exists()) {
            return back()->with('error', 'Cannot delete — department has assigned indicators.');
        }

        $department->delete();

        return redirect()->route('programs.departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    public function show(Request $request, Department $department)
    {
        $department->load(['indicators:id,code,title,indicator_tier_id', 'indicators.indicatorTier:id,name,prefix', 'parent:id,name', 'children:id,name,parent_id']);

        $indicatorSearch = $request->indicator_search ?? '';

        $availableIndicators = Indicator::with('indicatorTier:id,name,prefix')
            ->select('id', 'code', 'title', 'indicator_tier_id')
            ->when($indicatorSearch, fn($q) =>
                $q->where(fn($sub) =>
                    $sub->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($indicatorSearch) . '%'])
                        ->orWhereRaw('LOWER(title) LIKE ?', ['%' . strtolower($indicatorSearch) . '%'])
                )
            )
            ->whereNotIn('id', $department->indicators->pluck('id'))
            ->orderBy('code')
            ->get();

        return Inertia::render('Programs/Departments/Show', [
            'department'           => $department,
            'availableIndicators'  => $availableIndicators,
            'indicatorSearch'      => $indicatorSearch,
        ]);
    }

    public function assignIndicator(Request $request, Department $department)
    {
        $request->validate([
            'indicator_id' => 'required|exists:indicators,id',
        ]);

        $department->indicators()->syncWithoutDetaching([$request->indicator_id]);

        return redirect()->route('programs.departments.show', $department->id)
                        ->with('success', 'Indicator assigned successfully');
    }

    public function removeIndicator(Department $department, Indicator $indicator)
    {
        $department->indicators()->detach($indicator->id);

        return redirect()->route('programs.departments.show', $department->id)
                        ->with('success', 'Indicator removed successfully');
    }

    /**
     * Recursively hide fields from department and all its descendants
     */
    private function hideFieldsRecursively($department)
    {
        $department->makeHidden(['id', 'created_at', 'updated_at']);

        if ($department->descendants && $department->descendants->count() > 0) {
            $department->descendants = $department->descendants->map(function ($child) {
                return $this->hideFieldsRecursively($child);
            });
        }

        return $department;
    }
}
