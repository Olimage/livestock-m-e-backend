<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class DashboardController extends Controller
{

    public function login(Request $request){

        return redirect()->route('baseline-login')->with('error', 'Please login to continue.');

    }
  
    public function index(Request $request){

        $user = auth()->user();


        if($user->role == 'admin' || $user->role == 'super_admin'){

           return  $this->loadAdminDashboard();

        }

        // return Inertia::render('Dashboard');



    }


    public function loadAdminDashboard(){

        $stats = [
            'recordsSaved' => 12344, // Replace Record with your actual model
            'totalUsers' => User::count(),
            'dataPendingSync' => 23453 //Record::where('sync_status', 'pending')->count(), // Adjust based on your schema
        ];

            return Inertia::render('Dashboard/AdminDashboard', [
            'stats' => $stats
        ]);

    }
}

