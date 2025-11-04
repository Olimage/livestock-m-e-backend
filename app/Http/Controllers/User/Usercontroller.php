<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class Usercontroller extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->where('role', '!=', 'super_admin')
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'per_page', 'sort_by', 'sort_order'])
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


}
