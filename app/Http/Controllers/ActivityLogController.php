<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Get all activity logs with pagination
     */
    public function index(Request $request)
    {
        try {
            $query = ActivityLog::with('user:id,full_name,email')
                ->orderBy('created_at', 'desc');

            // Filter by user
            if ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            // Filter by action
            if ($request->has('action')) {
                $query->action($request->action);
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            // Filter by method
            if ($request->has('method')) {
                $query->where('method', strtoupper($request->method));
            }

            $perPage = $request->get('per_page', 15);
            $logs = $query->paginate($perPage);

            return response()->json([
                'status' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity logs for the authenticated user
     */
    public function myActivity(Request $request)
    {
        try {
            $user = Auth::user();

            $query = ActivityLog::byUser($user->id)
                ->orderBy('created_at', 'desc');

            // Filter by action
            if ($request->has('action')) {
                $query->action($request->action);
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            $perPage = $request->get('per_page', 15);
            $logs = $query->paginate($perPage);

            return response()->json([
                'status' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve your activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single activity log
     */
    public function show($id)
    {
        try {
            $log = ActivityLog::with('user:id,full_name,email')->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Activity log not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get activity statistics
     */
    public function statistics(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = ActivityLog::query();

            if ($userId) {
                $query->byUser($userId);
            }

            if ($startDate && $endDate) {
                $query->dateRange($startDate, $endDate);
            }

            $statistics = [
                'total_activities' => $query->count(),
                'by_method' => $query->selectRaw('method, COUNT(*) as count')
                    ->groupBy('method')
                    ->pluck('count', 'method'),
                'by_status' => $query->selectRaw('status_code, COUNT(*) as count')
                    ->groupBy('status_code')
                    ->pluck('count', 'status_code'),
                'unique_users' => $query->distinct('user_id')->count('user_id'),
                'recent_activities' => ActivityLog::with('user:id,full_name,email')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'status' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete old activity logs
     */
    public function cleanup(Request $request)
    {
        try {
            $days = $request->get('days', 90);
            $date = now()->subDays($days);

            $deleted = ActivityLog::where('created_at', '<', $date)->delete();

            return response()->json([
                'status' => true,
                'message' => "Deleted {$deleted} activity logs older than {$days} days"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to cleanup activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
