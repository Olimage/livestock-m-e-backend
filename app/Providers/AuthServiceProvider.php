<?php
namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => \App\Policies\PermissionPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // ✅ Grant all permissions to admins
        Gate::before(function (User $user, string $ability) {
            if ($user->is_admin) {
                return true;
            }
            return null; // Let normal permission checks handle the rest
        });

        // ✅ Register specific gates used in NavigationService
        $this->registerNavigationGates();

        // ✅ Dynamically register gates for all permissions from database
        $this->registerDynamicGates();
    }

    /**
     * Register gates specifically used in NavigationService
     */
    protected function registerNavigationGates()
    {
        // Reports permissions
        Gate::define('view-summary-reports', function (User $user) {
            return $user->hasPermission('view-summary-reports');
        });

        Gate::define('view-detailed-reports', function (User $user) {
            return $user->hasPermission('view-detailed-reports');
        });

        // Settings permissions
        Gate::define('manage-settings', function (User $user) {
            return $user->hasPermission('manage-settings');
        });

        Gate::define('manage-users', function (User $user) {
            return $user->hasPermission('manage-users');
        });

        Gate::define('manage-permissions', function (User $user) {
            return $user->hasPermission('manage-permissions');
        });
    }

    /**
     * Register gates dynamically from permissions table
     */
    protected function registerDynamicGates()
    {
        try {
            foreach (Permission::pluck('permission') as $permissionName) {
                Gate::define($permissionName, function (User $user) use ($permissionName) {
                    return $user->hasPermission($permissionName);
                });
            }
        } catch (\Exception $e) {
            // Handle case where permissions table doesn't exist yet (during migration)
            logger()->warning('Could not register dynamic gates: ' . $e->getMessage());
        }
    }
}