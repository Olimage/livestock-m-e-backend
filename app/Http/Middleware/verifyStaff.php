<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class verifyStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();

        if (! $user || ! $user->roles()->where('slug', 'staff')->exists()) {

            return response()->json([
                'status' => false,
                'message' => 'unauthorized',
                'error' => 'unauthorized',
            ], 403);

        }

        return $next($request);
    }
}
