<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{

    public function login(Request $request){

        return redirect()->route('baseline-login')->with('error', 'Please login to continue.');

    }
  
    public function index(Request $request){

        return Inertia::render('Dashboard');



    }
}
