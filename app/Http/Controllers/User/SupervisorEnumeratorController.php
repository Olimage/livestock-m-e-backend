<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupervisorEnumerator;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupervisorEnumeratorController extends Controller
{
    public function index()
    {
        $supervisors = User::where('role', 'supervisor')
            ->with(['supervisedEnumerators' => function($query) {
                $query->select('users.id', 'users.full_name', 'users.email');
            }])
            ->get();

        $availableEnumerators = User::where('role', 'enumerator')
            ->whereDoesntHave('supervisors')
            ->select('id', 'full_name', 'email')
            ->get();

        return Inertia::render('SupervisorEnumerator/Index', [
            'supervisors' => $supervisors,
            'availableEnumerators' => $availableEnumerators
        ]);
    }

    public function assignEnumerators(Request $request)
    {
        $data = $request->validate([
            'supervisor_id' => 'required|exists:users,id',
            'enumerator_ids' => 'required|array',
            'enumerator_ids.*' => 'exists:users,id'
        ]);

        // Verify supervisor role
        $supervisor = User::findOrFail($data['supervisor_id']);
        if (!$supervisor->isSupervisor()) {
            return back()->with('error', 'Selected user is not a supervisor');
        }

        // Verify all selected users are enumerators
        $enumerators = User::whereIn('id', $data['enumerator_ids'])
            ->where('role', 'enumerator')
            ->get();
        
        if ($enumerators->count() !== count($data['enumerator_ids'])) {
            return back()->with('error', 'Some selected users are not enumerators');
        }

        // Create assignments
        foreach ($data['enumerator_ids'] as $enumeratorId) {
            SupervisorEnumerator::create([
                'supervisor_id' => $data['supervisor_id'],
                'enumerator_id' => $enumeratorId
            ]);
        }

        return back()->with('success', 'Enumerators assigned successfully');
    }

    public function removeEnumerator(Request $request)
    {
        $data = $request->validate([
            'supervisor_id' => 'required|exists:users,id',
            'enumerator_id' => 'required|exists:users,id'
        ]);

        SupervisorEnumerator::where([
            'supervisor_id' => $data['supervisor_id'],
            'enumerator_id' => $data['enumerator_id']
        ])->delete();

        return back()->with('success', 'Enumerator removed from supervisor');
    }
}