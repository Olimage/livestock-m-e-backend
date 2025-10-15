<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Redirect authenticated users to dashboard
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}