<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class NavigationService
{
    public static function getNavigation()
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        $navItems = [];

        // Base navigation items available to all authenticated users
        $navItems[] = [
            'name'      => 'Dashboard',
            'routeName' => 'home',
            'icon'      => 'bi bi-speedometer2',
        ];

        // Data Creation Items
        if ($user->isAdmin() || $user->can('viewDataCreation', User::class)) {
            $navItems[] = [
                'name'      => 'A New Input',
                'routeName' => 'baseline-new',
                'icon'      => 'bi bi-plus-square',
            ];

            $navItems[] = [
                'name'      => 'Saved Data',
                'routeName' => 'baseline-saved-data',
                'icon'      => 'bi bi-floppy',
            ];
        }

        // Build Reports submenu based on permissions
        $reportsSubmenu = [];
        
        if ($user->isAdmin() || $user->can('view-summary-reports')) {
            $reportsSubmenu[] = [
                'name'      => 'Summary',
                'routeName' => 'baseline-saved-data',
                'icon'      => 'bi bi-file-text'
            ];
        }
        
        if ($user->isAdmin() || $user->can('view-detailed-reports')) {
            $reportsSubmenu[] = [
                'name'      => 'Detailed',
                'routeName' => 'baseline-saved-data',
                'icon'      => 'bi bi-file-earmark-spreadsheet'
            ];
        }

        if (!empty($reportsSubmenu)) {
            $navItems[] = [
                'name'    => 'Reports',
                'icon'    => 'bi bi-file-earmark-text',
                'submenu' => $reportsSubmenu,
            ];
        }

        // Settings (with submenus)
        $settingsSubmenu = [];

        if ($user->isAdmin() || $user->can('manage-settings')) {
            $settingsSubmenu[] = [
                'name'      => 'General',
                'routeName' => 'baseline-saved-data',
                'icon'      => 'bi bi-sliders',
            ];
        }

        if ($user->isAdmin() || $user->can('manage-users')) {
            $settingsSubmenu[] = [
                'name'      => 'Users',
                'routeName' => 'users.index',
                'icon'      => 'bi bi-people',
            ];
        }


        if ($user->isAdmin() || $user->can('manage-users')) {
            $settingsSubmenu[] = [
                'name'      => 'Supervisors & Enumerators',
                'routeName' => 'supervisor-enumerators.index',
                'icon'      => 'bi bi-arrow-right-circle-fill',
            ];
   


        }

        if ($user->isAdmin() || $user->can('manage-permissions')) {
            $settingsSubmenu[] = [
                'name'      => 'Permissions',
                'routeName' => 'baseline-saved-data',
                'icon'      => 'bi bi-shield-lock',
            ];
        }

        // Only add Settings menu if user has at least one settings permission
        if (!empty($settingsSubmenu)) {
            $navItems[] = [
                'name'    => 'Settings',
                'icon'    => 'bi bi-gear',
                'submenu' => $settingsSubmenu,
            ];
        }

        return $navItems;
    }
}