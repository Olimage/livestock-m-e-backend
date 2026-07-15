<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $permissions = Permission::with('module:id,name')
            ->when($request->search, fn ($q) => $q->where('permission', 'like', "%{$request->search}%")
                ->orWhere('label', 'like', "%{$request->search}%"))
            ->orderBy('permission')->paginate($request->per_page ?? 20)->withQueryString();

        return Inertia::render('Settings/Permissions/Index', [
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'per_page']),
            'totalCount' => Permission::count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Permissions/Create', [
            'modules' => Module::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'permission' => 'required|string|max:255|unique:permissions,permission',
            'label' => 'nullable|string|max:255',
            'module_id' => 'nullable|exists:modules,id',
            'description' => 'nullable|string',
        ]);

        Permission::create($data);

        return redirect()->route('permissions.index')->with('success', 'Permission created.');
    }

    public function edit(Permission $permission)
    {
        return Inertia::render('Settings/Permissions/Edit', [
            'permission' => $permission,
            'modules' => Module::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'permission' => 'required|string|max:255|unique:permissions,permission,'.$permission->id,
            'label' => 'nullable|string|max:255',
            'module_id' => 'nullable|exists:modules,id',
            'description' => 'nullable|string',
        ]);

        $permission->update($data);

        return redirect()->route('permissions.index')->with('success', 'Permission updated.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted.');
    }
}
