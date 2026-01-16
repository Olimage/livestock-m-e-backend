# Activity Logging System

This application now has a comprehensive activity logging system that automatically tracks all user activities **including which specific database records (models) they interact with**.

## Features

- **Automatic Logging**: All API requests are automatically logged via middleware
- **Model Tracking**: Automatically captures which model and record ID was acted upon
- **Database Actions**: Tracks the type of action (created, updated, deleted, viewed, accessed)
- **Manual Logging**: Use the `LogsActivity` trait for manual logging
- **Comprehensive Data**: Logs include user, action, method, URL, IP address, device, status code, model information, and custom properties
- **User Tracking**: Track activities by specific users
- **Statistics**: Get activity statistics and analytics
- **Cleanup**: Automated cleanup of old logs

## Database Structure

The `activity_logs` table contains:
- `user_id` - The user who performed the action
- `callable_type` - The model type that was acted upon (polymorphic)
- `callable_id` - The ID of the model that was acted upon (polymorphic)
- `db_action` - The database action performed (created, updated, deleted, viewed, accessed)
- `action` - The action performed (route name or method + path)
- `description` - Human-readable description
- `method` - HTTP method (GET, POST, PUT, DELETE, etc.)
- `url` - Full URL of the request
- `ip_address` - User's IP address
- `user_agent` - User's browser/device information
- `device` - Device type (Mobile, Tablet, Desktop)
- `location` - Geographic location (if configured)
- `properties` - JSON field for additional data
- `status_code` - HTTP response status code
- `timestamps` - Created and updated timestamps

## API Endpoints

### Get All Activity Logs
```
GET /api/v1/activity-logs
```

Query Parameters:
- `user_id` - Filter by user ID
- `action` - Filter by action
- `db_action` - Filter by database action (created, updated, deleted, viewed, accessed)
- `callable_type` - Filter by model type (e.g., App\Models\User)
- `method` - Filter by HTTP method
- `start_date` - Start date for date range filter
- `end_date` - End date for date range filter
- `per_page` - Items per page (default: 15)

### Get My Activity
```
GET /api/v1/activity-logs/my-activity
```

Returns activity logs for the authenticated user.

### Get Single Activity Log
```
GET /api/v1/activity-logs/{id}
```

### Get Activity Statistics
```
GET /api/v1/activity-logs/statistics
```

Query Parameters:
- `user_id` - Filter statistics by user
- `start_date` - Start date for date range
- `end_date` - End date for date range

Returns:
- Total activities count
- Activities by HTTP method
- Activities by database action (created, updated, deleted, etc.)
- Activities by model type
- Activities by status code
- Unique users count
- Recent activities

### Cleanup Old Logs
```
DELETE /api/v1/activity-logs/cleanup
```

Query Parameters:
- `days` - Delete logs older than X days (default: 90)

## Automatic Logging (Middleware)

The `LogUserActivity` middleware is automatically applied to all API routes. It logs:
- Every request made to the API
- User information (if authenticated)
- Request details (method, URL, IP, user agent)
- Response status code
- Request parameters (excluding sensitive fields like passwords)
- **Model information** - Automatically detects which model is being acted upon from route parameters
- **Database action** - Maps HTTP methods to database actions (POST=created, PUT/PATCH=updated, DELETE=deleted, GET=viewed)

### Skipping Routes

To skip logging for specific routes, modify the `shouldSkipLogging()` method in the middleware:

```php
protected function shouldSkipLogging(Request $request): bool
{
    $skipRoutes = [
        'sanctum/*',
        'api/health',
        'api/ping',
        // Add more routes to skip
    ];

    foreach ($skipRoutes as $pattern) {
        if ($request->is($pattern)) {
            return true;
        }
    }

    return false;
}
```

## Manual Logging with Trait

Use the `LogsActivity` trait in your models to automatically log create, update, and delete operations:

```php
use App\Traits\LogsActivity;

class YourModel extends Model
{
    use LogsActivity;
    
    // Model code...
}
```

### Manual Activity Logging

You can also manually log activities anywhere in your code:

```php
use App\Traits\LogsActivity;

class YourController extends Controller
{
    use LogsActivity;
    
    public function someAction()
    {
        // Your code...
        $model = Indicator::find(1);
        
        self::logActivity(
            'custom_action',
            'User performed a custom action',
            ['key' => 'value', 'data' => 'additional data'],
            $model,  // The model being acted upon
            'updated'  // The database action
        );
    }
}
```

Or directly:

```php
use App\Models\ActivityLog;

$indicator = Indicator::find(1);

ActivityLog::create([
    'user_id' => auth()->id(),
    'action' => 'custom_action',
    'description' => 'Description of what happened',
    'method' => request()->method(),
    'url' => request()->fullUrl(),
    'ip_address' => request()->ip(),
    'callable_type' => get_class($indicator),
    'callable_id' => $indicator->id,
    'db_action' => 'updated',
    'properties' => ['custom' => 'data'],
]);
```

## Usage Examples

### Get all activities for a specific user
```bash
GET /api/v1/activity-logs?user_id=123
```

### Get all POST requests
```bash
GET /api/v1/activity-logs?method=POST
```

### Get all created records
```bash
GET /api/v1/activity-logs?db_action=created
```

### Get all activities on Indicator model
```bash
GET /api/v1/activity-logs?callable_type=App\Models\Indicator
```

### Get activities in a date range
```bash
GET /api/v1/activity-logs?start_date=2026-01-01&end_date=2026-01-31
```

### Get my recent activities
```bash
GET /api/v1/activity-logs/my-activity?per_page=20
```

### Get activity statistics
```bash
GET /api/v1/activity-logs/statistics
```

### Delete logs older than 30 days
```bash
DELETE /api/v1/activity-logs/cleanup?days=30
```

## Model Methods

The `ActivityLog` model includes several helpful methods:

```php
// Filter by action
ActivityLog::action('user.login')->get();

// Filter by database action
ActivityLog::dbAction('created')->get();

// Filter by user
ActivityLog::byUser(123)->get();

// Filter by model type
ActivityLog::byCallable('App\Models\Indicator')->get();

// Filter by date range
ActivityLog::dateRange('2026-01-01', '2026-01-31')->get();

// Combine filters
ActivityLog::byUser(123)
    ->dbAction('updated')
    ->byCallable('App\Models\Indicator')
    ->dateRange('2026-01-01', '2026-01-31')
    ->get();

// Get the related model
$log = ActivityLog::with('callable')->first();
$model = $log->callable; // Returns the actual model instance if it still exists
```

## Best Practices

1. **Privacy**: The middleware automatically excludes sensitive fields (passwords, tokens) from logging
2. **Retention**: Regularly cleanup old logs using the cleanup endpoint
3. **Performance**: Add indexes to frequently queried columns
4. **Storage**: Monitor database size as logs can grow quickly
5. **Security**: Restrict access to activity log endpoints to admin users only

## Configuration

### Disable Logging for Specific Environments

Add this to your middleware if needed:

```php
if (app()->environment('testing')) {
    return $next($request);
}
```

### Custom Properties

Add custom properties to logs by modifying the `getProperties()` method in the middleware or passing them when using the trait.

## Monitoring

Use the statistics endpoint to monitor:
- Most active users
- Failed requests (4xx, 5xx status codes)
- API usage patterns
- Security incidents (multiple failed login attempts, etc.)

## Notes

- All API routes automatically log activities
- **Model tracking is automatic** - When route parameters include Eloquent models, they are automatically logged
- **Database actions are inferred** - HTTP methods are mapped to database actions (POST→created, PUT/PATCH→updated, DELETE→deleted, GET→viewed)
- Logs are stored in the `activity_logs` table with polymorphic relationships to track models
- The system is designed to fail silently to prevent breaking the application
- Consider implementing log rotation or archiving for production systems
- The `LogsActivity` trait automatically logs create, update, and delete operations on models that use it

## Example Activity Log Entry

When a user updates an Indicator record, the log will contain:
```json
{
  "id": 123,
  "user_id": 5,
  "callable_type": "App\\Models\\Indicator",
  "callable_id": 42,
  "db_action": "updated",
  "action": "PUT /api/v1/indicators/42",
  "description": "John Doe performed PUT request on /api/v1/indicators/42",
  "method": "PUT",
  "url": "https://example.com/api/v1/indicators/42",
  "properties": {
    "route": "indicators.update",
    "params": {"indicator": 42},
    "request_data": {"title": "New Title"}
  },
  "status_code": 200,
  "user": {
    "id": 5,
    "full_name": "John Doe",
    "email": "john@example.com"
  },
  "callable": {
    "id": 42,
    "code": "IND-001",
    "title": "New Title"
  }
}
```
