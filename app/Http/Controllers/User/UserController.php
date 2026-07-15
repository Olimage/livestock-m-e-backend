<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('departments')
            ->when($request->search, function ($query, $search) {
                $query
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->whereDoesntHave('roles', fn ($q) => $q->where('slug', 'super_admin'))
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        $usersCount = $users->total();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'userCount' => $usersCount,
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully');
    }

    public function getUserProfile()
    {
        $me = User::where('id', '=', auth()->user()->id)->first();

        return response()->json([
            'status' => true,
            'message' => 'profile fetched successfully',
            'data' => $me,
        ], 200);
    }

    /**
     * Show authenticated user's profile
     */
    public function profile()
    {
        $user = Auth::user()->load('departments');

        return Inertia::render('Profile/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Show password change form
     */
    public function editPassword()
    {
        return Inertia::render('Profile/PasswordEdit');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = Auth::user();

            // Check if current password matches
            if (! Hash::check($validated['current_password'], $user->password)) {
                return redirect()->back()->withErrors([
                    'current_password' => 'The current password is incorrect.',
                ]);
            }

            // Update password
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            return redirect()->route('profile.show')->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show form to create a new user
     */
    public function create(Request $request)
    {
        try {
            $roles = Role::where('slug', '!=', 'super_admin')
                ->orderBy('name')
                ->get();

            // load top level departments with their children for selector
            $departments = Department::whereNull('parent_id')
                ->with('children')
                ->orderBy('name')
                ->get();

            return Inertia::render('Users/Create', [
                'roles' => $roles,
                'departments' => $departments,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Persist a new user
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|string|exists:roles,slug',
                'department_ids' => 'required|array|min:1',
                'department_ids.*' => 'integer|exists:departments,id',
            ]);

            // default password
            $defaultPassword = '1234567890';

            $user = User::create([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'password' => Hash::make($defaultPassword),
            ]);

            // assign the selected role via the pivot
            $roleId = Role::where('slug', $data['role'])->value('id');
            if ($roleId) {
                $user->roles()->syncWithoutDetaching([$roleId]);
            }

            // attach all selected department ids (path)
            foreach ($data['department_ids'] as $deptId) {
                UserDepartment::create([
                    'user_id' => $user->id,
                    'department_id' => $deptId,
                ]);
            }

            return redirect()
                ->route('users.index')
                ->with('success', "User created successfully. Default password is {$defaultPassword}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the user access screen (roles, direct permissions, departments).
     */
    public function edit(User $user)
    {
        $user->load('roles:id', 'permissions:id', 'departments:id');

        return Inertia::render('Users/Edit', [
            'user' => $user->only(['id', 'full_name', 'email', 'is_admin']),
            'selectedRoleIds' => $user->roles->pluck('id'),
            'selectedPermissionIds' => $user->permissions->pluck('id'),
            'selectedDepartmentIds' => $user->departments->pluck('id'),
            'roles' => Role::orderBy('name')->get(['id', 'name']),
            'permissions' => Permission::orderBy('permission')->get(['id', 'permission', 'label']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the user's profile fields and sync roles / permissions / departments.
     */
    public function update(Request $request, User $user)
    {
        $actor = $request->user();

        // Only user managers may edit users at all.
        abort_unless($actor && ($actor->is_admin || $actor->hasPermission('manage-users')), 403);

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'is_admin' => 'boolean',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        // Always-allowed profile fields.
        // Privilege-granting fields (is_admin, roles, direct permissions, and even
        // the email — an account-takeover vector via password reset) may only be
        // changed by a full admin.
        if ($actor->is_admin) {
            $payload = [
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'is_admin' => $data['is_admin'] ?? false,
            ];

            $roleIds = $data['role_ids'] ?? [];

            // Only a super_admin may grant the super_admin/admin roles.
            if (! $actor->roles()->where('slug', 'super_admin')->exists()) {
                $privilegedRoleIds = Role::whereIn('slug', ['super_admin', 'admin'])->pluck('id')->all();
                $keepPrivileged = $user->roles()->whereIn('slug', ['super_admin', 'admin'])->pluck('roles.id')->all();
                $roleIds = array_values(array_unique(array_merge(
                    array_diff($roleIds, $privilegedRoleIds), // requested, minus privileged
                    $keepPrivileged                            // preserve any the target already has
                )));
            }

            $user->update($payload);
            $user->roles()->sync($roleIds);
            $user->permissions()->sync($data['permission_ids'] ?? []);
        } else {
            // Non-admin manager (manage-users). Guardrails:
            //  - cannot touch a more-privileged user (admin / super_admin / admin role),
            //  - cannot change the email (account-takeover vector),
            //  - may only edit the display name and department assignments.
            if ($user->is_admin || $user->roles()->whereIn('slug', ['super_admin', 'admin'])->exists()) {
                abort(403, 'Cannot modify a privileged user.');
            }
            if ($data['email'] !== $user->email) {
                abort(403, 'Only administrators may change a user\'s email.');
            }

            $user->update(['full_name' => $data['full_name']]);
        }

        $user->departments()->sync($data['department_ids'] ?? []);

        return redirect()->route('users.index')->with('success', 'User updated.');
    }
}
