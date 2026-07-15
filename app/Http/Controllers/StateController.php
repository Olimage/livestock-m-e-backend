<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StateController extends Controller
{
    public function index(Request $request)
    {
        $states = State::with('zone:id,name')->withCount('lgas')
            ->when($request->zone_id, fn ($q) => $q->where('zone_id', $request->zone_id))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')->paginate($request->per_page ?? 20)->withQueryString();

        return Inertia::render('Settings/Geography/States/Index', [
            'states' => $states,
            'zones' => Zone::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'zone_id', 'per_page']),
            'totalCount' => State::count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Geography/States/Create', [
            'zones' => Zone::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
        ]);
        State::create($data);

        return redirect()->route('states.index')->with('success', 'State created.');
    }

    public function edit(State $state)
    {
        return Inertia::render('Settings/Geography/States/Edit', [
            'state' => $state,
            'zones' => Zone::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, State $state)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
        ]);
        $state->update($data);

        return redirect()->route('states.index')->with('success', 'State updated.');
    }

    public function destroy(State $state)
    {
        if ($state->lgas()->exists()) {
            return back()->with('error', 'Cannot delete — state has LGAs.');
        }
        $state->delete();

        return redirect()->route('states.index')->with('success', 'State deleted.');
    }
}
