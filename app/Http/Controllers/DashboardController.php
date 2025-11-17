<?php

namespace App\Http\Controllers;

use App\Models\BondOutcome;
use App\Models\Department;
use App\Models\EnumerationRecord;
use App\Models\MokData;
use App\Models\NlgasPillar;
use App\Models\PresidentialPriority;
use App\Models\SectoralGoal;
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

        $totalDataPendingSync = EnumerationRecord::where('sync_status', EnumerationRecord::SYNC_PENDING)->count();
        $totalDataSaved = EnumerationRecord::count();

        $TotalbondOutcomesCount = BondOutcome::count();
        $TotalpresidentialPrioritiesCount = PresidentialPriority::count();
        $TotalsectoralGoalsCount = SectoralGoal::count();
        $TotalnlgasPillarsCount = NlgasPillar::count();

        $stats = [
            [
                'label' => 'Total Users',
                'value' => User::where('role', '!=', 'super_admin')->count(),
                'icon' => 'bi bi-people-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Records Saved',
                'value' => $totalDataSaved,
                'icon' => 'bi bi-database-fill-check',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Data Pending Sync',
                'value' => $totalDataPendingSync,
                'icon' => 'bi bi-cloud-arrow-up-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Pending'
            ],
            [
                'label' => 'Bond Outcomes',
                'value' => $TotalbondOutcomesCount,
                'icon' => 'bi bi-flag-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Presidential Priorities',
                'value' => $TotalpresidentialPrioritiesCount,
                'icon' => 'bi bi-star-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Sectoral Goals',
                'value' => $TotalsectoralGoalsCount,
                'icon' => 'bi bi-bullseye',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'NLGAS Pillars',
                'value' => $TotalnlgasPillarsCount,
                'icon' => 'bi bi-building-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
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

        $totalDataPendingSync = EnumerationRecord::where('sync_status', EnumerationRecord::SYNC_PENDING)->count();
        $totalDataSaved = EnumerationRecord::count();
        $TotalbondOutcomesCount = BondOutcome::count();
        $TotalpresidentialPrioritiesCount = PresidentialPriority::count();
        $TotalsectoralGoalsCount = SectoralGoal::count();
        $TotalnlgasPillarsCount = NlgasPillar::count();

        $stats = [
            [
                'label' => 'Total Users',
                'value' => User::where('role', '!=', 'super_admin')->count(),
                'icon' => 'bi bi-people-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Records Saved',
                'value' => $totalDataSaved,
                'icon' => 'bi bi-database-fill-check',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Data Pending Sync',
                'value' => $totalDataPendingSync,
                'icon' => 'bi bi-cloud-arrow-up-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Pending'
            ],
            [
                'label' => 'Bond Outcomes',
                'value' => $TotalbondOutcomesCount,
                'icon' => 'bi bi-flag-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Presidential Priorities',
                'value' => $TotalpresidentialPrioritiesCount,
                'icon' => 'bi bi-star-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'Sectoral Goals',
                'value' => $TotalsectoralGoalsCount,
                'icon' => 'bi bi-bullseye',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ],
            [
                'label' => 'NLGAS Pillars',
                'value' => $TotalnlgasPillarsCount,
                'icon' => 'bi bi-building-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active'
            ]
        ];

        return response()->json($stats);
    }
}
