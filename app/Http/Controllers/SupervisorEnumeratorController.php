<?php

namespace App\Http\Controllers;

use App\Models\SupervisorEnumerator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SupervisorEnumeratorController extends Controller
{
    public function webIndex()
    {
        // Get supervisors with their enumerators
        $supervisors = User::where('role', 'supervisor')
            ->with(['enumerators' => function ($query) {
                $query->select('users.id', 'users.full_name', 'users.email');
            }])
            ->select('id', 'full_name', 'email')
            ->get()
            ->map(function ($supervisor) {
                return [
                    'id' => $supervisor->id,
                    'full_name' => $supervisor->full_name,
                    'email' => $supervisor->email,
                    'enumerators' => $supervisor->enumerators->map(function ($enumerator) {
                        return [
                            'id' => $enumerator->id,
                            'full_name' => $enumerator->full_name,
                            'email' => $enumerator->email,
                        ];
                    })->values()->all()
                ];
            })
            ->values()
            ->all();

        // Get available enumerators (not assigned to any supervisor)
        $availableEnumerators = User::where('role', 'enumerator')
            ->whereDoesntHave('supervisor')
            ->select('id', 'full_name', 'email')
            ->get()
            ->map(function ($enumerator) {
                return [
                    'id' => $enumerator->id,
                    'full_name' => $enumerator->full_name,
                    'email' => $enumerator->email,
                ];
            })
            ->values()
            ->all();

        return Inertia::render('SupervisorEnumerator/Index', [
            'supervisors' => $supervisors,
            'availableEnumerators' => $availableEnumerators,
        ]);
    }

    public function webCreate()
    {
        $supervisors = User::where('role', 'supervisor')->get();
        $enumerators = User::where('role', 'enumerator')->get();

        return Inertia::render('SupervisorEnumerator/Create', [
            'supervisors' => $supervisors,
            'enumerators' => $enumerators,
        ]);
    }

    public function index()
    {
        $relationships = SupervisorEnumerator::with(['supervisor', 'enumerator'])
            ->get()
            ->map(function ($relation) {
                return [
                    'id' => $relation->id,
                    'supervisor' => [
                        'id' => $relation->supervisor->id,
                        'name' => $relation->supervisor->name,
                        'email' => $relation->supervisor->email,
                    ],
                    'enumerator' => [
                        'id' => $relation->enumerator->id,
                        'name' => $relation->enumerator->name,
                        'email' => $relation->enumerator->email,
                    ],
                    'created_at' => $relation->created_at,
                ];
            });

        return response()->json($relationships);
    }

    public function assign(Request $request)
    {
        try {
            $data = $request->validate([
                'supervisor_id' => 'required|exists:users,id',
                'enumerator_ids' => 'required|array|min:1',
                'enumerator_ids.*' => 'required|exists:users,id',
            ]);

            // Verify the supervisor role
            $supervisor = User::findOrFail($data['supervisor_id']);
            if ($supervisor->role !== 'supervisor') {
                throw ValidationException::withMessages([
                    'supervisor_id' => ['Selected user is not a supervisor'],
                ]);
            }

            // Load enumerators and verify roles
            $enumerators = User::whereIn('id', $data['enumerator_ids'])->get();
            $invalid = $enumerators->filter(function ($u) {
                return $u->role !== 'enumerator';
            })->pluck('id')->all();

            if (!empty($invalid)) {
                throw ValidationException::withMessages([
                    'enumerator_ids' => ['One or more selected users are not enumerators'],
                ]);
            }

            $created = [];

            DB::transaction(function () use ($data, $supervisor, &$created) {
                foreach ($data['enumerator_ids'] as $enumeratorId) {
                    $exists = SupervisorEnumerator::where([
                        'supervisor_id' => $supervisor->id,
                        'enumerator_id' => $enumeratorId,
                    ])->exists();

                    if (!$exists) {
                        $relationship = SupervisorEnumerator::create([
                            'supervisor_id' => $supervisor->id,
                            'enumerator_id' => $enumeratorId,
                        ]);
                        $created[] = $relationship->load(['enumerator']);
                    }
                }
            });

            return back()->with('success', 'Enumerators assigned successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function remove($supervisorId, $enumeratorId)
    {
        // Find the relationship using route params
        $relationship = SupervisorEnumerator::where([
            'supervisor_id' => $supervisorId,
            'enumerator_id' => $enumeratorId,
        ])->first();

        if (! $relationship) {
            return back()->with('error', 'No such supervisor-enumerator relationship found');
        }

        $relationship->delete();

        return back()->with('success', 'Enumerator unassigned successfully');
    }

    public function getEnumerators($supervisorId)
    {
        $supervisor = User::findOrFail($supervisorId);
        if ($supervisor->role !== 'supervisor') {
            throw ValidationException::withMessages([
                'supervisor_id' => ['Selected user is not a supervisor'],
            ]);
        }

        $enumerators = SupervisorEnumerator::where('supervisor_id', $supervisorId)
            ->with('enumerator')
            ->get()
            ->map(function ($relation) {
                return [
                    'id' => $relation->enumerator->id,
                    'name' => $relation->enumerator->full_name,
                    'email' => $relation->enumerator->email,
                    'assigned_at' => $relation->created_at,
                ];
            });

        return response()->json($enumerators);
    }
}
