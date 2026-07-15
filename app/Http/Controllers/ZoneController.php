<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $zones = Zone::withCount('states')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')->paginate($request->per_page ?? 15)->withQueryString();

        return Inertia::render('Settings/Geography/Zones/Index', [
            'zones' => $zones,
            'filters' => $request->only(['search', 'per_page']),
            'totalCount' => Zone::count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Geography/Zones/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);
        Zone::create($data);

        return redirect()->route('zones.index')->with('success', 'Zone created.');
    }

    public function edit(Zone $zone)
    {
        return Inertia::render('Settings/Geography/Zones/Edit', ['zone' => $zone]);
    }

    public function update(Request $request, Zone $zone)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);
        $zone->update($data);

        return redirect()->route('zones.index')->with('success', 'Zone updated.');
    }

    public function destroy(Zone $zone)
    {
        if ($zone->states()->exists()) {
            return back()->with('error', 'Cannot delete — zone has states.');
        }
        $zone->delete();

        return redirect()->route('zones.index')->with('success', 'Zone deleted.');
    }
}
