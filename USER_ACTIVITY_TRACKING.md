# User Activity Tracking Guide

## Overview

The activity logging system automatically tracks which user performs each action. Every activity log entry includes the `user_id` of the authenticated user who performed the action.

## Database Structure

The `activity_logs` table has a `user_id` foreign key that references the `users` table:
- When a user is authenticated, their ID is automatically recorded
- For guest/unauthenticated requests, `user_id` is `null`

## Relationships

### From ActivityLog to User
```php
$activityLog = ActivityLog::find(1);
$user = $activityLog->user; // Get the user who performed this activity
```

### From User to ActivityLogs
```php
$user = User::find(1);
$activities = $user->activityLogs; // Get all activities by this user
$recentActivities = $user->recentActivity(20); // Get 20 most recent activities
```

## Usage Examples

### 1. Get All Activities for a Specific User
```php
use App\Models\User;

$user = User::find(5);

// Get all activities
$allActivities = $user->activityLogs;

// Get recent activities (default 10)
$recent = $user->recentActivity();

// Get custom number of recent activities
$recent20 = $user->recentActivity(20);

// With additional filtering
$userCreatedRecords = $user->activityLogs()
    ->where('db_action', 'created')
    ->get();
```

### 2. Get User Information from Activity Log
```php
use App\Models\ActivityLog;

$log = ActivityLog::with('user')->find(100);

echo $log->user->full_name; // "John Doe"
echo $log->user->email; // "john@example.com"
```

### 3. Query Activity Logs by User
```php
// Get all activities by user ID
$activities = ActivityLog::where('user_id', 5)->get();

// Using the scope
$activities = ActivityLog::byUser(5)->get();

// With eager loading
$activities = ActivityLog::with('user')
    ->byUser(5)
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```

### 4. Get Activity Statistics for a User
```php
$user = User::find(5);

// Total activities
$totalActivities = $user->activityLogs()->count();

// Activities by action
$byAction = $user->activityLogs()
    ->selectRaw('db_action, COUNT(*) as count')
    ->whereNotNull('db_action')
    ->groupBy('db_action')
    ->pluck('count', 'db_action');

// Recent created records
$recentCreated = $user->activityLogs()
    ->where('db_action', 'created')
    ->with('callable')
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();
```

### 5. API Endpoint Examples

#### Get My Activity
```bash
GET /api/v1/activity-logs/my-activity
Authorization: Bearer {token}
```

Response:
```json
{
  "status": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 150,
        "user_id": 5,
        "callable_type": "App\\Models\\Indicator",
        "callable_id": 42,
        "db_action": "updated",
        "action": "PUT /api/v1/indicators/42",
        "description": "John Doe performed PUT request on /api/v1/indicators/42",
        "user": {
          "id": 5,
          "full_name": "John Doe",
          "email": "john@example.com"
        },
        "callable": {
          "id": 42,
          "code": "IND-001",
          "title": "Updated Indicator"
        }
      }
    ]
  }
}
```

#### Get Activities for a Specific User (Admin)
```bash
GET /api/v1/activity-logs?user_id=5
Authorization: Bearer {admin_token}
```

### 6. Track User Actions in Controllers

The middleware automatically tracks the authenticated user:

```php
// In your controller
public function update(Request $request, Indicator $indicator)
{
    // Update the indicator
    $indicator->update($request->validated());
    
    // The activity is automatically logged with:
    // - user_id: auth()->id()
    // - callable_type: App\Models\Indicator
    // - callable_id: $indicator->id
    // - db_action: updated
    // - all other request details
    
    return response()->json([
        'status' => true,
        'data' => $indicator
    ]);
}
```

### 7. Manual Logging with User Tracking

```php
use App\Models\ActivityLog;

// Manual logging (user_id is automatically included)
ActivityLog::create([
    'user_id' => auth()->id(), // Current authenticated user
    'action' => 'custom_action',
    'description' => 'User performed a custom action',
    'callable_type' => 'App\\Models\\Indicator',
    'callable_id' => 42,
    'db_action' => 'viewed',
    'properties' => ['custom' => 'data']
]);
```

### 8. Using the LogsActivity Trait

When models use the `LogsActivity` trait, user information is automatically included:

```php
use App\Models\Indicator;

// This will automatically log with the current user's ID
$indicator = Indicator::create([
    'code' => 'IND-001',
    'title' => 'New Indicator'
]);

// The activity log will have:
// - user_id: auth()->id()
// - db_action: 'created'
// - callable_type: 'App\Models\Indicator'
// - callable_id: $indicator->id
```

## Authentication Flow

1. **User logs in** → JWT token is issued
2. **User makes API request** → Token is verified in middleware
3. **Action is performed** → Activity is logged with `user_id`
4. **Response is returned** → User's activity is tracked

## Guest Users

For unauthenticated requests:
- `user_id` is stored as `null`
- IP address and user agent are still tracked
- You can filter for guest activities with `whereNull('user_id')`

```php
// Get all guest activities
$guestActivities = ActivityLog::whereNull('user_id')->get();

// Get all authenticated user activities
$userActivities = ActivityLog::whereNotNull('user_id')->get();
```

## Security & Privacy

### Best Practices

1. **Access Control**: Only allow users to view their own activity logs
2. **Admin Override**: Admins can view all users' activity logs
3. **Data Retention**: Regularly cleanup old logs (use the cleanup endpoint)
4. **Sensitive Data**: The middleware excludes passwords and tokens from logging

### Example: Restrict Access in Controller

```php
public function myActivity(Request $request)
{
    $user = Auth::user();
    
    // Users can only see their own activity
    $logs = ActivityLog::byUser($user->id)
        ->with('callable')
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    return response()->json([
        'status' => true,
        'data' => $logs
    ]);
}

public function index(Request $request)
{
    // Only admins can view all users' activities
    if (!auth()->user()->isAdmin()) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
    
    // Admin logic here...
}
```

## Complete Example: User Dashboard

```php
public function userDashboard()
{
    $user = auth()->user();
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email
        ],
        'activity_summary' => [
            'total_activities' => $user->activityLogs()->count(),
            'recent_activity' => $user->recentActivity(5),
            'created_count' => $user->activityLogs()->where('db_action', 'created')->count(),
            'updated_count' => $user->activityLogs()->where('db_action', 'updated')->count(),
            'deleted_count' => $user->activityLogs()->where('db_action', 'deleted')->count(),
            'last_activity' => $user->activityLogs()->latest()->first()
        ]
    ]);
}
```

## Summary

✅ **User tracking is fully implemented:**
- Every activity log includes `user_id` of the authenticated user
- Bidirectional relationships: User → ActivityLogs and ActivityLog → User
- Automatic tracking via middleware for all API requests
- Manual tracking support with user context
- Helper methods on User model for easy access to activity logs
- API endpoints to view user-specific activities
- Guest activity tracking for unauthenticated requests

The system automatically captures and links every action to the exact user who performed it!
