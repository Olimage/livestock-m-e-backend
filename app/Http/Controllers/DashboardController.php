<?php

namespace App\Http\Controllers;

use App\Models\EnumerationRecord;
use App\Models\MokData;
use App\Models\NlgasPillar;
use App\Models\SectoralGoal;
use App\Models\User;
use App\Support\ResultChainIndicators;
use Illuminate\Http\Request;
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

        if ($user->is_admin || $user->roles()->whereIn('slug', ['admin', 'super_admin'])->exists()) {
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

        $TotalsectoralGoalsCount = SectoralGoal::count();
        $TotalnlgasPillarsCount = NlgasPillar::count();

        $stats = [
            [
                'label' => 'Total Users',
                'value' => User::whereDoesntHave('roles', fn ($q) => $q->where('slug', 'super_admin'))->count(),
                'icon' => 'bi bi-people-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active',
            ],
            [
                'label' => 'Total Indicators',
                'value' => collect(ResultChainIndicators::TYPES)->keys()->sum(fn ($class) => $class::count()),
                'icon' => 'bi bi-bar-chart-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active',
            ],

            [
                'label' => 'Sectoral Goals',
                'value' => $TotalsectoralGoalsCount,
                'icon' => 'bi bi-bullseye',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active',
            ],
            [
                'label' => 'NLGAS Pillars',
                'value' => $TotalnlgasPillarsCount,
                'icon' => 'bi bi-columns',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active',
            ],
        ];

        return Inertia::render('Dashboard/AdminDashboard', [
            'stats' => $stats,
        ]);
    }

    public function getStats()
    {
        $data = MokData::where('name', 'LIKE', '%stat_%')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('value', 'name');

        $totalDataPendingSync = EnumerationRecord::where('sync_status', EnumerationRecord::SYNC_PENDING)->count();
        $TotalsectoralGoalsCount = SectoralGoal::count();
        $TotalnlgasPillarsCount = NlgasPillar::count();

        $stats = [
            [
                'label' => 'Total Users',
                'value' => User::whereDoesntHave('roles', fn ($q) => $q->where('slug', 'super_admin'))->count(),
                'icon' => 'bi bi-people-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active',
            ],
            [
                'label' => 'Total Indicators',
                'value' => collect(ResultChainIndicators::TYPES)->keys()->sum(fn ($class) => $class::count()),
                'icon' => 'bi bi-bar-chart-fill',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active',
            ],

            [
                'label' => 'Sectoral Goals',
                'value' => $TotalsectoralGoalsCount,
                'icon' => 'bi bi-bullseye',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active',
            ],
            [
                'label' => 'NLGAS Pillars',
                'value' => $TotalnlgasPillarsCount,
                'icon' => 'bi bi-columns',
                'gradient' => 'from-emerald-500 to-teal-600',
                'bgColor' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'badge' => 'Active',
            ],
        ];

        return response()->json($stats);
    }
}
