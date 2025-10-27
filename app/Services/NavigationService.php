<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class NavigationService
{
    public static function getNavigation()
    {
        $user = Auth::user();

          $navItems = [];
        
        // Base navigation items available to all authenticated users
      $navItems[] = [
            'name' => 'Dashboard',
            'routeName' => 'home',
            'icon' => 'bi bi-speedometer2',
        ];

      

    // if ($user->can('create-data')) {
        $navItems[] = [
            'name' => 'A New Input',
            'routeName' => 'baseline-new',
            'icon' => 'bi bi-plus-square',
        ];
    // }
    

    if ($user->can('create-data')) {
        $navItems[] = [
            'name' => 'Saved Data',
            'routeName' => 'baseline-saved-data',
            'icon' => 'bi bi-plus-square',
        ];
    }

    
   // Build Reports submenu based on permissions
    $reportsSubmenu = [];
    if ($user->can('view-summary-reports')) {
        $reportsSubmenu[] = ['name' => 'Summary', 'routeName' => 'baseline-reports-summary', 'icon' => 'bi bi-file-text'];
    }
    if ($user->can('view-detailed-reports')) {
        $reportsSubmenu[] = ['name' => 'Detailed', 'routeName' => 'baseline-reports-detailed', 'icon' => 'bi bi-file-earmark-spreadsheet'];
    }
    
    if (!empty($reportsSubmenu)) {
        $navItems[] = [
            'name' => 'Reports',
            'icon' => 'bi bi-file-earmark-text',
            'submenu' => $reportsSubmenu,
        ];
    }

        return $navItems;
    }
}