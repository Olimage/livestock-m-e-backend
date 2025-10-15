<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if (!$request->expectsJson()) {
                session()->put('url.intended', $request->url());
            }
            
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        return $next($request);
    }
}