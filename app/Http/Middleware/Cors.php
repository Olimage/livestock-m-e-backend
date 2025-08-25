<?php
namespace App\Http\Middleware;

use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        if ($request->getMethod() === "OPTIONS") {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        $response->headers->set('Access-Control-Allow-Origin', "*");
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, Authorization, Origin');
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Length, Content-Range, X-Content-Range, X-Requested-With, X-Progress');

        return $response;
    }
    
}