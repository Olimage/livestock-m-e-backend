<?php
namespace App\Models;

use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes, HasPermissions;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'uuid',
        'is_admin',
        'role'

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        // 'created_at',
        'updated_at',
        'deleted_at',
        // 'id'
    ];

    protected $casts = [
         'is_admin' => 'boolean',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }


    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function department(){

        return $this->hasMany(Department::class);
    }

    /**
     * Departments the user belongs to (via user_departments pivot)
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'user_departments', 'user_id', 'department_id');
    }

 public function permissions()
{
    return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
}

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {

        if ($this->is_admin) {
            return true;
        }

        return $this->permissions()->where('permission', $permission)->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_admin) {
            return true;
        }
        return $this->permissions()->whereIn('permission', $permissions)->exists();
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {

        if ($this->is_admin) {
            return true;
        }
        return $this->permissions()->whereIn('permission', $permissions)->count() === count($permissions);
    }

    /**
     * Get all permission names as array
     */
    public function getPermissionNames(): array
    {

        if ($this->is_admin) {
            // Return all available permissions for admins
            return \App\Models\Permission::pluck('permission')->toArray();
        }

        return $this->permissions()->pluck('permission')->toArray();
    }

      /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function supervisor()
    {
        return $this->belongsToMany(
            User::class,
            'supervisor_enumerators',
            'enumerator_id',
            'supervisor_id'
        );
    }

    public function enumerators()
    {
        return $this->belongsToMany(
            User::class,
            'supervisor_enumerators',
            'supervisor_id',
            'enumerator_id'
        );
    }

    /**
     * Get all activity logs for this user
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get recent activity logs for this user
     */
    public function recentActivity($limit = 10)
    {
        return $this->activityLogs()
            ->with('callable')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

}
