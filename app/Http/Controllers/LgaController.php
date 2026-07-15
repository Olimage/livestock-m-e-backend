<?php

namespace App\Http\Controllers;

use App\Models\Lga;
use App\Models\State;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LgaController extends Controller
{
    public function index(Request $request)
    {
        $lgas = Lga::with('state:id,name,zone_id', 'state.zone:id,name')
            ->when($request->state_id, fn ($q) => $q->where('state_id', $request->state_id))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')->paginate($request->per_page ?? 25)->withQueryString();

        return Inertia::render('Settings/Geography/Lgas/Index', [
            'lgas' => $lgas,
            'states' => State::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'state_id', 'per_page']),
            'totalCount' => Lga::count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Geography/Lgas/Create', [
            'states' => State::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
        ]);
        Lga::create($data);

        return redirect()->route('lgas.index')->with('success', 'LGA created.');
    }

    public function edit(Lga $lga)
    {
        return Inertia::render('Settings/Geography/Lgas/Edit', [
            'lga' => $lga,
            'states' => State::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Lga $lga)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
        ]);
        $lga->update($data);

        return redirect()->route('lgas.index')->with('success', 'LGA updated.');
    }

    public function destroy(Lga $lga)
    {
        $lga->delete();

        return redirect()->route('lgas.index')->with('success', 'LGA deleted.');
    }
}
