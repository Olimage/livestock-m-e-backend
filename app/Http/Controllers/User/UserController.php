<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Role;
use App\Models\Department;
use App\Models\UserDepartment;


class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('departments')
            ->when($request->search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->where('role', '!=', 'super_admin')
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

            $usersCount = $users->where('role', '!=', 'super_admin')->count();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order']),
            'userCount' => $usersCount
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully');
    }

    public function getUserProfile(){


        $me = User::where('id', '=', auth()->user()->id)->first();

        return response()->json([
            'status'  => true,
            'message' => 'profile fetched successfully',
            'data' => $me,
        ], 200);

    }

    

    /**
     * Show form to create a new user
     */
    public function create(Request $request)
    {
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
    }

    /**
     * Persist a new user
     */
    public function store(Request $request)
    {
        try{
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
            'department_ids' => 'required|array|min:1',
            'department_ids.*' => 'integer|exists:departments,id',
        ]);

        // default password
        $defaultPassword = '1234567890';

        $user = User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($defaultPassword),
        ]);

        // attach all selected department ids (path)
        foreach ($data['department_ids'] as $deptId) {
            UserDepartment::create([
                'user_id' => $user->id,
                'department_id' => $deptId,
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', "User created successfully. Default password is {$defaultPassword}");
    } catch (\Exception $e) {
        return redirect()->back()->with('error',  $e->getMessage());
    }
}


}
