<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::withCount(['permissions', 'users'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')->paginate($request->per_page ?? 15)->withQueryString();

        return Inertia::render('Settings/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'per_page']),
            'totalCount' => Role::count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Roles/Create', [
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name'], '_'),
        ]);
        $role->permissions()->sync($data['permission_ids'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        return Inertia::render('Settings/Roles/Edit', [
            'role' => $role,
            'selectedPermissionIds' => $role->permissions()->pluck('permissions.id'),
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,'.$role->id,
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $data['name'], 'slug' => $data['slug']]);
        $role->permissions()->sync($data['permission_ids'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    /**
     * Permissions grouped by module label for the checkbox grid.
     *
     * @return array<int, array{group:string, permissions:array}>
     */
    private function permissionGroups(): array
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');

        return Permission::orderBy('permission')->get(['id', 'permission', 'label', 'module_id'])
            ->groupBy(fn ($p) => $p->module_id ? ($modules[$p->module_id] ?? 'Other') : 'General')
            ->map(fn ($group, $label) => [
                'group' => $label,
                'permissions' => $group->map(fn ($p) => [
                    'id' => $p->id,
                    'permission' => $p->permission,
                    'label' => $p->label ?? $p->permission,
                ])->values(),
            ])->values()->all();
    }
}
