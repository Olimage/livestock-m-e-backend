<?php

namespace App\Http\Controllers;

use App\Models\BondDeliverable;
use App\Models\BondOutputIndicator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BondDeliverableController extends Controller
{
    public function index(Request $request)
    {
        $query = BondDeliverable::withCount('bondOutputIndicators');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('deliverable', 'like', "%{$request->search}%");
            });
        }

        $allowedSorts = ['code', 'deliverable', 'created_at'];
        $sortBy = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : 'code';
        $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';

        $bondDeliverables = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        return Inertia::render('Programs/BondDeliverables/Index', [
            'bondDeliverables' => $bondDeliverables,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'totalCount' => BondDeliverable::count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Programs/BondDeliverables/Create', [
            'indicators' => BondOutputIndicator::orderBy('code')->get(['id', 'code', 'title'])
                ->map(fn ($i) => ['id' => $i->id, 'code' => $i->code, 'title' => $i->title]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:bond_deliverables,code',
            'deliverable' => 'required|string',
            'indicator_ids' => 'nullable|array',
            'indicator_ids.*' => 'exists:bond_output_indicators,id',
        ]);

        $bd = BondDeliverable::create([
            'code' => $data['code'],
            'deliverable' => $data['deliverable'],
        ]);

        if (! empty($data['indicator_ids'])) {
            $bd->bondOutputIndicators()->sync($data['indicator_ids']);
        }

        return redirect()->route('programs.bond-deliverables.index')
            ->with('success', 'Bond deliverable created successfully.');
    }

    public function edit(BondDeliverable $bondDeliverable)
    {
        $bondDeliverable->load('bondOutputIndicators:id');

        return Inertia::render('Programs/BondDeliverables/Edit', [
            'bondDeliverable' => $bondDeliverable,
            'selectedIds' => $bondDeliverable->bondOutputIndicators->pluck('id')->toArray(),
            'indicators' => BondOutputIndicator::orderBy('code')->get(['id', 'code', 'title'])
                ->map(fn ($i) => ['id' => $i->id, 'code' => $i->code, 'title' => $i->title]),
        ]);
    }

    public function update(Request $request, BondDeliverable $bondDeliverable)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:bond_deliverables,code,'.$bondDeliverable->id,
            'deliverable' => 'required|string',
            'indicator_ids' => 'nullable|array',
            'indicator_ids.*' => 'exists:bond_output_indicators,id',
        ]);

        $bondDeliverable->update([
            'code' => $data['code'],
            'deliverable' => $data['deliverable'],
        ]);

        $bondDeliverable->bondOutputIndicators()->sync($data['indicator_ids'] ?? []);

        return redirect()->route('programs.bond-deliverables.index')
            ->with('success', 'Bond deliverable updated successfully.');
    }

    public function destroy(BondDeliverable $bondDeliverable)
    {
        $bondDeliverable->bondOutputIndicators()->detach();
        $bondDeliverable->delete();

        return redirect()->route('programs.bond-deliverables.index')
            ->with('success', 'Bond deliverable deleted successfully.');
    }
}
