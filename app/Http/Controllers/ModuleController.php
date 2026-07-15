<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $modules = Module::withCount('permissions')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')->paginate($request->per_page ?? 15)->withQueryString();

        return Inertia::render('Settings/Modules/Index', [
            'modules' => $modules,
            'filters' => $request->only(['search', 'per_page']),
            'totalCount' => Module::count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Modules/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:modules,slug',
            'description' => 'nullable|string',
        ]);

        Module::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('modules.index')->with('success', 'Module created.');
    }

    public function edit(Module $module)
    {
        return Inertia::render('Settings/Modules/Edit', ['module' => $module]);
    }

    public function update(Request $request, Module $module)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:modules,slug,'.$module->id,
            'description' => 'nullable|string',
        ]);

        $module->update($data);

        return redirect()->route('modules.index')->with('success', 'Module updated.');
    }

    public function destroy(Module $module)
    {
        $module->delete();

        return redirect()->route('modules.index')->with('success', 'Module deleted.');
    }
}
