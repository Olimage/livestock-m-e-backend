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
            'name'      => 'Overview',
            'routeName' => 'home',
            'icon'      => 'bi bi-speedometer2',
        ];

 
        // Enumerations (single entry; create actions available on index page)
        // if ($user->isAdmin() || $user->can('manage-enumerations')) {
        //     $navItems[] = [
        //         'name'      => 'Enumerations',
        //         'routeName' => 'enumerations.index',
        //         'icon'      => 'bi bi-list-check',
        //     ];
        // }

        // Build Reports submenu based on permissions
        $reportsSubmenu = [];
        
        // if ($user->isAdmin() || $user->can('view-summary-reports')) {
        //     $reportsSubmenu[] = [
        //         'name'      => 'Summary',
        //         'routeName' => 'baseline-saved-data',
        //         'icon'      => 'bi bi-file-text'
        //     ];
        // }
        
        // if ($user->isAdmin() || $user->can('view-detailed-reports')) {
        //     $reportsSubmenu[] = [
        //         'name'      => 'Detailed',
        //         'routeName' => 'baseline-saved-data',
        //         'icon'      => 'bi bi-file-earmark-spreadsheet'
        //     ];
        // }

        // if (!empty($reportsSubmenu)) {
        //     $navItems[] = [
        //         'name'    => 'Reports',
        //         'icon'    => 'bi bi-file-earmark-text',
        //         'submenu' => $reportsSubmenu,
        //     ];
        // }

        // Result Chain
        $resultChainItems = self::resultChainMenu($user);
        foreach ($resultChainItems as $rcItem) {
            $navItems[] = $rcItem;
        }

        // Programs (with submenus)
        $programsSubmenu = self::programsMenu($user);

        if (!empty($programsSubmenu)) {
            $navItems[] = [
                'name'    => 'System',
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

    public static function resultChainMenu($user)
    {
        if (!$user->isAdmin() && !$user->can('manage-programs')) {
            return [];
        }

        // Return a single top-level item with a flat submenu using section headers
        return [
            [
                'name'    => 'Result Chain',
                'icon'    => 'bi bi-diagram-2',
                'submenu' => [
                    [
                        'name'      => 'Inputs',
                        'routeName' => 'result-chain.inputs.index',
                        'icon'      => 'bi bi-box-arrow-in-down',
                    ],
                    ['section' => 'Activities'],
                    [
                        'name'      => 'Programs',
                        'routeName' => 'programs.programs.index',
                        'icon'      => 'bi bi-folder',
                    ],
                    [
                        'name'      => 'Activities',
                        'routeName' => 'result-chain.activities.index',
                        'icon'      => 'bi bi-lightning',
                    ],
                    ['section' => 'Outputs'],
                    [
                        'name'      => 'Output Indicators',
                        'routeName' => 'result-chain.output-indicators.index',
                        'icon'      => 'bi bi-graph-up-arrow',
                    ],
                    [
                        'name'      => 'Bond Output Indicators',
                        'routeName' => 'result-chain.bond-output-indicators.index',
                        'icon'      => 'bi bi-bookmark-check',
                    ],
                    [
                        'name'      => 'Program Output Indicators',
                        'routeName' => 'result-chain.program-output-indicators.index',
                        'icon'      => 'bi bi-folder-check',
                    ],
                    ['section' => 'Outcomes'],
                    [
                        'name'      => 'Outcome Indicators',
                        'routeName' => 'result-chain.outcome-indicators.index',
                        'icon'      => 'bi bi-graph-up',
                    ],
                    [
                        'name'      => 'Sectorial Goals',
                        'routeName' => 'programs.sectoral-goals.index',
                        'icon'      => 'bi bi-flag',
                    ],
                    ['section' => 'Impacts'],
                    [
                        'name'      => 'Impact Indicators',
                        'routeName' => 'result-chain.impact-indicators.index',
                        'icon'      => 'bi bi-bar-chart-line',
                    ],
                    [
                        'name'      => 'Presidential Priorities',
                        'routeName' => 'programs.presidential-priorities.index',
                        'icon'      => 'bi bi-star',
                    ],
                ],
            ],
        ];
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
                'name'      => 'Indicator Types',
                'routeName' => 'programs.indicator-tiers.index',
                'icon'      => 'bi bi-tags',
            ];

            $programsSubmenu[] = [
                'name'      => 'Tiers',
                'routeName' => 'programs.tiers.index',
                'icon'      => 'bi bi-layers',
            ];

            $programsSubmenu[] = [
                'name'      => 'Bond Deliverables',
                'routeName' => 'programs.bond-deliverables.index',
                'icon'      => 'bi bi-bookmark-check',
            ];

            $programsSubmenu[] = [
                'name'      => 'Cross-Cutting Metrics',
                'routeName' => 'programs.cross-cutting-metrics.index',
                'icon'      => 'bi bi-grid-3x3',
            ];

            $programsSubmenu[] = [
                'name'      => 'Disaggregations',
                'routeName' => 'programs.disagregations.index',
                'icon'      => 'bi bi-funnel',
            ];

            $programsSubmenu[] = [
                'name'      => 'Departments',
                'routeName' => 'programs.departments.index',
                'icon'      => 'bi bi-building',
            ];

            $programsSubmenu[] = [
                'name'      => 'Baselines',
                'routeName' => 'programs.baselines.index',
                'icon'      => 'bi bi-bar-chart-steps',
            ];
        }

        return $programsSubmenu;
    }
}