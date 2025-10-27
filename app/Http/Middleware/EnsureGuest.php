<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\DashboardController;


class EnsureGuest
{
    /**
     * Handle an incoming request.
     *
     * Redirects authenticated users away from guest-only pages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Redirect authenticated users to dashboard

                        return (new DashboardController)->index($request);
        }

        return $next($request);
    }
}