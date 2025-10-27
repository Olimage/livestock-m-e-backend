<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

       /**
     * Perform pre-authorization checks.
     * Admins can do everything!
     */
    public function before(User $user, string $ability)
    {
        if ($user->is_admin) {
            return true;
        }
    }


        public function viewDataCreation(User $user): bool
    {
        return $user->permissions()->whereIn('name', [
            'super_admin.create', 'admin.create', 'hod.create', 
            'baseline_dashboard.create', 'baseline_mobile.create'
        ])->exists();
    }

    

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(User $user, string $permissionName)
    {
          if ($user->is_admin) {
            return true;
        }
        return $user->permissions()->where('permission', $permissionName)->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(User $user, array $permissionNames)
    {
        return $user->permissions()->whereIn('permission', $permissionNames)->exists();
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(User $user, array $permissionNames)
    {
        return $user->permissions()->whereIn('permission', $permissionNames)->count() === count($permissionNames);
    }
}