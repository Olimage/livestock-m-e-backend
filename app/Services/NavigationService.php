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

 
        // Enumerations (single entry; create actions available on index page)
        if ($user->isAdmin() || $user->can('manage-enumerations')) {
            $navItems[] = [
                'name'      => 'Enumerations',
                'routeName' => 'enumerations.index',
                'icon'      => 'bi bi-list-check',
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

        // Programs (with submenus)
        $programsSubmenu = self::programsMenu($user);

        if (!empty($programsSubmenu)) {
            $navItems[] = [
                'name'    => 'Programs',
                'icon'    => 'bi bi-diagram-3',
                'submenu' => $programsSubmenu,
            ];
        }


        // Settings (with submenus)
        $settingsSubmenu = self::settingMenu($user);

    

        if (!empty($settingsSubmenu)) {
            $navItems[] = [
                'name'    => 'Settings',
                'icon'    => 'bi bi-gear',
                'submenu' => $settingsSubmenu,
            ];
        }

        return $navItems;
    }


      public static function settingMenu($user){

        
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

        return $settingsSubmenu;


    }

    public static function programsMenu($user)
    {
        $programsSubmenu = [];

        if ($user->isAdmin() || $user->can('manage-programs')) {
            $programsSubmenu[] = [
                'name'      => 'Presidential Priorities',
                'routeName' => 'programs.presidential-priorities.index',
                'icon'      => 'bi bi-star',
            ];

            $programsSubmenu[] = [
                'name'      => 'Sectoral Goals',
                'routeName' => 'programs.sectoral-goals.index',
                'icon'      => 'bi bi-bullseye',
            ];

            $programsSubmenu[] = [
                'name'      => 'Bond Outcomes',
                'routeName' => 'programs.bond-outcomes.index',
                'icon'      => 'bi bi-trophy',
            ];

            $programsSubmenu[] = [
                'name'      => 'NLGAS Pillars',
                'routeName' => 'programs.nlgas-pillars.index',
                'icon'      => 'bi bi-columns',
            ];

            $programsSubmenu[] = [
                'name'      => 'Programs',
                'routeName' => 'programs.programs.index',
                'icon'      => 'bi bi-folder',
            ];

            $programsSubmenu[] = [
                'name'      => 'Indicators',
                'routeName' => 'programs.indicators.index',
                'icon'      => 'bi bi-graph-up',
            ];

            $programsSubmenu[] = [
                'name'      => 'Tiers',
                'routeName' => 'programs.tiers.index',
                'icon'      => 'bi bi-layers',
            ];

            $programsSubmenu[] = [
                'name'      => 'Cross-Cutting Metrics',
                'routeName' => 'programs.cross-cutting-metrics.index',
                'icon'      => 'bi bi-grid-3x3',
            ];
        }

        return $programsSubmenu;
    }
}