<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\DashboardController;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            if (!$request->expectsJson()) {
                session()->put('url.intended', $request->url());
            }

            return (new DashboardController)->login($request);
            
            // return redirect()->route('baseline-login')->with('error', 'Please login to continue.');
        }

        return $next($request);
    }
}