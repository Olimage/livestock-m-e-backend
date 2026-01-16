<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log the activity after the response
        $this->logActivity($request, $response);

        return $response;
    }

    /**
     * Log the user activity
     */
    protected function logActivity(Request $request, Response $response): void
    {
        try {
            $user = Auth::user();
            
            // Skip logging for certain routes if needed
            if ($this->shouldSkipLogging($request)) {
                return;
            }

            ActivityLog::create([
                'user_id' => $user?->id,
                'action' => $this->getAction($request),
                'description' => $this->getDescription($request),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device' => $this->getDevice($request),
                'location' => $this->getLocation($request),
                'properties' => $this->getProperties($request),
                'status_code' => $response->getStatusCode(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to prevent breaking the application
            \Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    /**
     * Determine if logging should be skipped for this request
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        // Skip logging for specific routes
        $skipRoutes = [
            'sanctum/*',
            'api/health',
            'api/ping',
        ];

        foreach ($skipRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a human-readable action from the request
     */
    protected function getAction(Request $request): string
    {
        $method = strtolower($request->method());
        $path = $request->path();

        // Try to generate a meaningful action name
        $routeName = $request->route()?->getName();
        if ($routeName) {
            return $routeName;
        }

        return $method . ' ' . $path;
    }

    /**
     * Get a description of the action
     */
    protected function getDescription(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();
        $user = Auth::user();

        return $user ? "{$user->full_name} performed {$method} request on {$path}" : "Guest performed {$method} request on {$path}";
    }

    /**
     * Extract device information from user agent
     */
    protected function getDevice(Request $request): ?string
    {
        $userAgent = $request->userAgent();
        
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        }
        
        return 'Desktop';
    }

    /**
     * Get location from IP (placeholder - integrate with a geolocation service if needed)
     */
    protected function getLocation(Request $request): ?string
    {
        // You can integrate with a geolocation service here
        return null;
    }

    /**
     * Get additional properties to log
     */
    protected function getProperties(Request $request): array
    {
        $properties = [
            'route' => $request->route()?->getName(),
            'params' => $request->route()?->parameters(),
        ];

        // Include request data for POST, PUT, PATCH (excluding sensitive fields)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $data = $request->except(['password', 'password_confirmation', 'token', 'current_password']);
            if (!empty($data)) {
                $properties['request_data'] = $data;
            }
        }

        return $properties;
    }
}
