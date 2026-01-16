<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    /**
     * Log an activity
     *
     * @param string $action
     * @param string|null $description
     * @param array $properties
     * @return ActivityLog
     */
    public static function logActivity(string $action, ?string $description = null, array $properties = []): ActivityLog
    {
        $user = Auth::user();
        
        return ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'method' => Request::method(),
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'properties' => $properties,
        ]);
    }

    /**
     * Log activity after model is created
     */
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            if (method_exists($model, 'shouldLogActivity') && !$model->shouldLogActivity('created')) {
                return;
            }

            self::logActivity(
                'created',
                class_basename($model) . ' created',
                ['model' => class_basename($model), 'id' => $model->id]
            );
        });

        static::updated(function ($model) {
            if (method_exists($model, 'shouldLogActivity') && !$model->shouldLogActivity('updated')) {
                return;
            }

            self::logActivity(
                'updated',
                class_basename($model) . ' updated',
                ['model' => class_basename($model), 'id' => $model->id, 'changes' => $model->getChanges()]
            );
        });

        static::deleted(function ($model) {
            if (method_exists($model, 'shouldLogActivity') && !$model->shouldLogActivity('deleted')) {
                return;
            }

            self::logActivity(
                'deleted',
                class_basename($model) . ' deleted',
                ['model' => class_basename($model), 'id' => $model->id]
            );
        });
    }
}
