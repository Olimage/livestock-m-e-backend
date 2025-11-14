<?php

namespace App\Http\Controllers;

use App\Models\MokData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function login(Request $request)
    {
        return redirect()->route('baseline-login')->with('error', 'Please login to continue.');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role == 'admin' || $user->role == 'super_admin') {
            return $this->loadAdminDashboard();
        }

        // return Inertia::render('Dashboard');
    }

    public function loadAdminDashboard()
    {
        $data = MokData::where('name', 'LIKE', '%stat_%')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('value', 'name');

        $stats = [
            [
                'label' => 'Records Saved',
                'value' => $data['stat_recordsSaved'] ?? 0,
                'icon' => 'bi bi-database-fill-check',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Total Users',
                'value' => User::where('role', '!=', 'super_admin')->count(),
                'icon' => 'bi bi-people-fill',
                'gradient' => 'from-blue-500 to-indigo-600',
                'bgColor' => 'bg-blue-50',
                'iconColor' => 'text-blue-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Data Pending Sync',
                'value' => $data['stat_dataPendingSync'] ?? 0,
                'icon' => 'bi bi-cloud-arrow-up-fill',
                'gradient' => 'from-amber-500 to-orange-600',
                'bgColor' => 'bg-amber-50',
                'iconColor' => 'text-amber-600',
                'badge' => 'Pending'
            ]
        ];

        return Inertia::render('Dashboard/AdminDashboard', [
            'stats' => $stats
        ]);
    }

    public function getStats()
    {
        $data = MokData::where('name', 'LIKE', '%stat_%')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('value', 'name');

        $stats = [
            [
                'label' => 'Records Saved',
                'value' => $data['stat_recordsSaved'] ?? 0,
                'icon' => 'bi bi-database-fill-check',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Total Users',
                'value' => User::where('role', '!=', 'super_admin')->count(),
                'icon' => 'bi bi-people-fill',
                'gradient' => 'from-blue-500 to-indigo-600',
                'bgColor' => 'bg-blue-50',
                'iconColor' => 'text-blue-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Data Pending Sync',
                'value' => $data['stat_dataPendingSync'] ?? 0,
                'icon' => 'bi bi-cloud-arrow-up-fill',
                'gradient' => 'from-amber-500 to-orange-600',
                'bgColor' => 'bg-amber-50',
                'iconColor' => 'text-amber-600',
                'badge' => 'Pending'
            ]
        ];

        return response()->json($stats);
    }
}
