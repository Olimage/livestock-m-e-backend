<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BroadcastController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Http\Controllers\User\UserController;
use App\Models\Department;
use App\Http\Controllers\EnumerationController;
use App\Http\Controllers\LocationController;


Route::get('/', [DashboardController::class, 'index'])->name('home')->middleware('auth.web');
Route::post('/login', [LoginController::class, 'login'])->name('app.login')->middleware('guest.web');
Route::get('/logout', [LoginController::class, 'logout'])->name('app.logout')->middleware('auth.web');


Route::get('login', function () {
    return Inertia::render('Login', [
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status'),
    ]);
})->name('baseline-login')->middleware('guest.web');



   

    Route::get('/new', function () {
        return Inertia::render('Enumeration/NewEnumeration', [
            'routeName' => Route::currentRouteName(),
        ]);
    })->name('baseline-new')->middleware('auth.web');

    Route::get('/saved-data', function () {
    })->name('baseline-saved-data')->middleware('auth.web');

    
Route::middleware(['auth.web'])->group(function () {
    
    // Location API endpoints for cascading selectors
    Route::prefix('location')->name('location.')->group(function () {
        Route::get('/zones', [LocationController::class, 'zones'])->name('zones');
        Route::get('/states', [LocationController::class, 'states'])->name('states');
        Route::get('/lgas', [LocationController::class, 'lgas'])->name('lgas');
    });
    
    // Return immediate children of a department (used by cascading selectors)
    Route::get('/departments/{id}/children', function ($id) {
        $children = Department::where('parent_id', $id)->orderBy('name')->get();
        return response()->json($children);
    })->name('departments.children');
    
    // Lightweight broadcast/test endpoints (auth required)
    Route::get('/broadcast/live', [BroadcastController::class, 'sendLiveData'])->name('broadcast.live');
    Route::get('/broadcast/notify/{user}', [BroadcastController::class, 'notifyUser'])->name('broadcast.notify');
    
    // Profile Routes
    Route::get('/profile', [UserController::class, 'profile'])->name('profile.show');
    Route::get('/profile/password', [UserController::class, 'editPassword'])->name('profile.password.edit');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password.update');
    
    Route::prefix('settings')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        
        // Supervisor-Enumerator Management
        require __DIR__.'/v1/web-supervisor-enumerator.php';
    });

    // Program Management Routes
    Route::prefix('programs')->name('programs.')->group(function () {
        // Presidential Priorities
        Route::get('/presidential-priorities', [\App\Http\Controllers\ProgramController::class, 'presidentialPriorities'])->name('presidential-priorities.index');
        Route::get('/presidential-priorities/create', [\App\Http\Controllers\ProgramController::class, 'createPresidentialPriority'])->name('presidential-priorities.create');
        Route::post('/presidential-priorities', [\App\Http\Controllers\ProgramController::class, 'storePresidentialPriority'])->name('presidential-priorities.store');
        Route::get('/presidential-priorities/{priority}/edit', [\App\Http\Controllers\ProgramController::class, 'editPresidentialPriority'])->name('presidential-priorities.edit');
        Route::put('/presidential-priorities/{priority}', [\App\Http\Controllers\ProgramController::class, 'updatePresidentialPriority'])->name('presidential-priorities.update');
        Route::delete('/presidential-priorities/{priority}', [\App\Http\Controllers\ProgramController::class, 'destroyPresidentialPriority'])->name('presidential-priorities.destroy');

        // Sectoral Goals
        Route::get('/sectoral-goals', [\App\Http\Controllers\ProgramController::class, 'sectoralGoals'])->name('sectoral-goals.index');
        Route::get('/sectoral-goals/create', [\App\Http\Controllers\ProgramController::class, 'createSectoralGoal'])->name('sectoral-goals.create');
        Route::post('/sectoral-goals', [\App\Http\Controllers\ProgramController::class, 'storeSectoralGoal'])->name('sectoral-goals.store');
        Route::get('/sectoral-goals/{goal}/edit', [\App\Http\Controllers\ProgramController::class, 'editSectoralGoal'])->name('sectoral-goals.edit');
        Route::put('/sectoral-goals/{goal}', [\App\Http\Controllers\ProgramController::class, 'updateSectoralGoal'])->name('sectoral-goals.update');
        Route::delete('/sectoral-goals/{goal}', [\App\Http\Controllers\ProgramController::class, 'destroySectoralGoal'])->name('sectoral-goals.destroy');

        // NLGAS Pillars
        Route::get('/nlgas-pillars', [\App\Http\Controllers\ProgramController::class, 'nlgasPillars'])->name('nlgas-pillars.index');
        Route::get('/nlgas-pillars/create', [\App\Http\Controllers\ProgramController::class, 'createNlgasPillar'])->name('nlgas-pillars.create');
        Route::post('/nlgas-pillars', [\App\Http\Controllers\ProgramController::class, 'storeNlgasPillar'])->name('nlgas-pillars.store');
        Route::get('/nlgas-pillars/{pillar}/edit', [\App\Http\Controllers\ProgramController::class, 'editNlgasPillar'])->name('nlgas-pillars.edit');
        Route::put('/nlgas-pillars/{pillar}', [\App\Http\Controllers\ProgramController::class, 'updateNlgasPillar'])->name('nlgas-pillars.update');
        Route::delete('/nlgas-pillars/{pillar}', [\App\Http\Controllers\ProgramController::class, 'destroyNlgasPillar'])->name('nlgas-pillars.destroy');

        // Programs
        Route::get('/programs', [\App\Http\Controllers\ProgramController::class, 'programs'])->name('programs.index');
        Route::get('/programs/create', [\App\Http\Controllers\ProgramController::class, 'createProgram'])->name('programs.create');
        Route::post('/programs', [\App\Http\Controllers\ProgramController::class, 'storeProgram'])->name('programs.store');
        Route::get('/programs/{program}/edit', [\App\Http\Controllers\ProgramController::class, 'editProgram'])->name('programs.edit');
        Route::put('/programs/{program}', [\App\Http\Controllers\ProgramController::class, 'updateProgram'])->name('programs.update');
        Route::delete('/programs/{program}', [\App\Http\Controllers\ProgramController::class, 'destroyProgram'])->name('programs.destroy');

        // Indicators
        Route::get('/indicators', [\App\Http\Controllers\ProgramController::class, 'indicators'])->name('indicators.index');
        Route::get('/indicators/create', [\App\Http\Controllers\ProgramController::class, 'createIndicator'])->name('indicators.create');
        Route::post('/indicators', [\App\Http\Controllers\ProgramController::class, 'storeIndicator'])->name('indicators.store');
        Route::get('/indicators/{indicator}/edit', [\App\Http\Controllers\ProgramController::class, 'editIndicator'])->name('indicators.edit');
        Route::put('/indicators/{indicator}', [\App\Http\Controllers\ProgramController::class, 'updateIndicator'])->name('indicators.update');
        Route::delete('/indicators/{indicator}', [\App\Http\Controllers\ProgramController::class, 'destroyIndicator'])->name('indicators.destroy');

        // Indicator Tiers
        Route::get('/indicator-tiers', [\App\Http\Controllers\ProgramController::class, 'indicatorTiers'])->name('indicator-tiers.index');
        Route::get('/indicator-tiers/create', [\App\Http\Controllers\ProgramController::class, 'createIndicatorTier'])->name('indicator-tiers.create');
        Route::post('/indicator-tiers', [\App\Http\Controllers\ProgramController::class, 'storeIndicatorTier'])->name('indicator-tiers.store');
        Route::get('/indicator-tiers/{indicatorTier}/edit', [\App\Http\Controllers\ProgramController::class, 'editIndicatorTier'])->name('indicator-tiers.edit');
        Route::put('/indicator-tiers/{indicatorTier}', [\App\Http\Controllers\ProgramController::class, 'updateIndicatorTier'])->name('indicator-tiers.update');
        Route::delete('/indicator-tiers/{indicatorTier}', [\App\Http\Controllers\ProgramController::class, 'destroyIndicatorTier'])->name('indicator-tiers.destroy');

        // Tiers
        Route::get('/tiers', [\App\Http\Controllers\ProgramController::class, 'tiers'])->name('tiers.index');
        Route::get('/tiers/create', [\App\Http\Controllers\ProgramController::class, 'createTier'])->name('tiers.create');
        Route::post('/tiers', [\App\Http\Controllers\ProgramController::class, 'storeTier'])->name('tiers.store');
        Route::get('/tiers/{tier}/edit', [\App\Http\Controllers\ProgramController::class, 'editTier'])->name('tiers.edit');
        Route::put('/tiers/{tier}', [\App\Http\Controllers\ProgramController::class, 'updateTier'])->name('tiers.update');
        Route::delete('/tiers/{tier}', [\App\Http\Controllers\ProgramController::class, 'destroyTier'])->name('tiers.destroy');

        // Departments
        Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [\App\Http\Controllers\DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departments', [\App\Http\Controllers\DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{department}/edit', [\App\Http\Controllers\DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.destroy');
        Route::get('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'show'])->name('departments.show');
        Route::post('/departments/{department}/indicators', [\App\Http\Controllers\DepartmentController::class, 'assignIndicator'])->name('departments.indicators.assign');
        Route::delete('/departments/{department}/indicators/{indicator}', [\App\Http\Controllers\DepartmentController::class, 'removeIndicator'])->name('departments.indicators.remove');

        // Indicator Baseline Years
        Route::get('/baselines', [\App\Http\Controllers\ProgramController::class, 'baselines'])->name('baselines.index');
        Route::get('/baselines/create', [\App\Http\Controllers\ProgramController::class, 'createBaseline'])->name('baselines.create');
        Route::post('/baselines', [\App\Http\Controllers\ProgramController::class, 'storeBaseline'])->name('baselines.store');
        Route::get('/baselines/{baseline}/edit', [\App\Http\Controllers\ProgramController::class, 'editBaseline'])->name('baselines.edit');
        Route::put('/baselines/{baseline}', [\App\Http\Controllers\ProgramController::class, 'updateBaseline'])->name('baselines.update');
        Route::delete('/baselines/{baseline}', [\App\Http\Controllers\ProgramController::class, 'destroyBaseline'])->name('baselines.destroy');

        // Disaggregation Categories & Items
        Route::get('/disagregations', [\App\Http\Controllers\ProgramController::class, 'disagregationCategories'])->name('disagregations.index');
        Route::get('/disagregations/create', [\App\Http\Controllers\ProgramController::class, 'createDisagregationCategory'])->name('disagregations.create');
        Route::post('/disagregations', [\App\Http\Controllers\ProgramController::class, 'storeDisagregationCategory'])->name('disagregations.store');
        Route::get('/disagregations/{category}/edit', [\App\Http\Controllers\ProgramController::class, 'editDisagregationCategory'])->name('disagregations.edit');
        Route::put('/disagregations/{category}', [\App\Http\Controllers\ProgramController::class, 'updateDisagregationCategory'])->name('disagregations.update');
        Route::delete('/disagregations/{category}', [\App\Http\Controllers\ProgramController::class, 'destroyDisagregationCategory'])->name('disagregations.destroy');
        Route::post('/disagregations/{category}/items', [\App\Http\Controllers\ProgramController::class, 'storeDisagregationItem'])->name('disagregations.items.store');
        Route::put('/disagregations/{category}/items/{item}', [\App\Http\Controllers\ProgramController::class, 'updateDisagregationItem'])->name('disagregations.items.update');
        Route::delete('/disagregations/{category}/items/{item}', [\App\Http\Controllers\ProgramController::class, 'destroyDisagregationItem'])->name('disagregations.items.destroy');

        // Bond Deliverables
        Route::get('/bond-deliverables', [\App\Http\Controllers\BondDeliverableController::class, 'index'])->name('bond-deliverables.index');
        Route::get('/bond-deliverables/create', [\App\Http\Controllers\BondDeliverableController::class, 'create'])->name('bond-deliverables.create');
        Route::post('/bond-deliverables', [\App\Http\Controllers\BondDeliverableController::class, 'store'])->name('bond-deliverables.store');
        Route::get('/bond-deliverables/{bondDeliverable}/edit', [\App\Http\Controllers\BondDeliverableController::class, 'edit'])->name('bond-deliverables.edit');
        Route::put('/bond-deliverables/{bondDeliverable}', [\App\Http\Controllers\BondDeliverableController::class, 'update'])->name('bond-deliverables.update');
        Route::delete('/bond-deliverables/{bondDeliverable}', [\App\Http\Controllers\BondDeliverableController::class, 'destroy'])->name('bond-deliverables.destroy');

        // Cross-Cutting Metrics
        Route::get('/cross-cutting-metrics', [\App\Http\Controllers\ProgramController::class, 'crossCuttingMetrics'])->name('cross-cutting-metrics.index');
        Route::get('/cross-cutting-metrics/create', [\App\Http\Controllers\ProgramController::class, 'createCrossCuttingMetric'])->name('cross-cutting-metrics.create');
        Route::post('/cross-cutting-metrics', [\App\Http\Controllers\ProgramController::class, 'storeCrossCuttingMetric'])->name('cross-cutting-metrics.store');
        Route::get('/cross-cutting-metrics/{crossCuttingMetric}/edit', [\App\Http\Controllers\ProgramController::class, 'editCrossCuttingMetric'])->name('cross-cutting-metrics.edit');
        Route::put('/cross-cutting-metrics/{crossCuttingMetric}', [\App\Http\Controllers\ProgramController::class, 'updateCrossCuttingMetric'])->name('cross-cutting-metrics.update');
        Route::delete('/cross-cutting-metrics/{crossCuttingMetric}', [\App\Http\Controllers\ProgramController::class, 'destroyCrossCuttingMetric'])->name('cross-cutting-metrics.destroy');
    });

    // Result Chain Routes
    Route::prefix('result-chain')->name('result-chain.')->group(function () {
        // Inputs
        Route::get('/inputs', [\App\Http\Controllers\ResultChainController::class, 'inputs'])->name('inputs.index');

        // Activities
        Route::get('/activities', [\App\Http\Controllers\ResultChainController::class, 'activities'])->name('activities.index');
        Route::get('/activities/create', [\App\Http\Controllers\ResultChainController::class, 'createActivity'])->name('activities.create');
        Route::post('/activities', [\App\Http\Controllers\ResultChainController::class, 'storeActivity'])->name('activities.store');
        Route::get('/activities/{activity}/edit', [\App\Http\Controllers\ResultChainController::class, 'editActivity'])->name('activities.edit');
        Route::put('/activities/{activity}', [\App\Http\Controllers\ResultChainController::class, 'updateActivity'])->name('activities.update');
        Route::delete('/activities/{activity}', [\App\Http\Controllers\ResultChainController::class, 'destroyActivity'])->name('activities.destroy');

        // Output Indicators
        Route::get('/output-indicators', [\App\Http\Controllers\ResultChainController::class, 'outputIndicators'])->name('output-indicators.index');
        Route::get('/output-indicators/create', [\App\Http\Controllers\ResultChainController::class, 'createOutputIndicator'])->name('output-indicators.create');
        Route::post('/output-indicators', [\App\Http\Controllers\ResultChainController::class, 'storeOutputIndicator'])->name('output-indicators.store');
        Route::get('/output-indicators/{outputIndicator}/edit', [\App\Http\Controllers\ResultChainController::class, 'editOutputIndicator'])->name('output-indicators.edit');
        Route::put('/output-indicators/{outputIndicator}', [\App\Http\Controllers\ResultChainController::class, 'updateOutputIndicator'])->name('output-indicators.update');
        Route::delete('/output-indicators/{outputIndicator}', [\App\Http\Controllers\ResultChainController::class, 'destroyOutputIndicator'])->name('output-indicators.destroy');

        // Bond Output Indicators
        Route::get('/bond-output-indicators', [\App\Http\Controllers\ResultChainController::class, 'bondOutputIndicators'])->name('bond-output-indicators.index');
        Route::get('/bond-output-indicators/create', [\App\Http\Controllers\ResultChainController::class, 'createBondOutputIndicator'])->name('bond-output-indicators.create');
        Route::post('/bond-output-indicators', [\App\Http\Controllers\ResultChainController::class, 'storeBondOutputIndicator'])->name('bond-output-indicators.store');
        Route::get('/bond-output-indicators/{bondOutputIndicator}/edit', [\App\Http\Controllers\ResultChainController::class, 'editBondOutputIndicator'])->name('bond-output-indicators.edit');
        Route::put('/bond-output-indicators/{bondOutputIndicator}', [\App\Http\Controllers\ResultChainController::class, 'updateBondOutputIndicator'])->name('bond-output-indicators.update');
        Route::delete('/bond-output-indicators/{bondOutputIndicator}', [\App\Http\Controllers\ResultChainController::class, 'destroyBondOutputIndicator'])->name('bond-output-indicators.destroy');

        // Pillar Program Output Indicators
        Route::get('/program-output-indicators', [\App\Http\Controllers\ResultChainController::class, 'pillarProgramOutputIndicators'])->name('program-output-indicators.index');
        Route::get('/program-output-indicators/create', [\App\Http\Controllers\ResultChainController::class, 'createPillarProgramOutputIndicator'])->name('program-output-indicators.create');
        Route::post('/program-output-indicators', [\App\Http\Controllers\ResultChainController::class, 'storePillarProgramOutputIndicator'])->name('program-output-indicators.store');
        Route::get('/program-output-indicators/{pillarProgramOutputIndicator}/edit', [\App\Http\Controllers\ResultChainController::class, 'editPillarProgramOutputIndicator'])->name('program-output-indicators.edit');
        Route::put('/program-output-indicators/{pillarProgramOutputIndicator}', [\App\Http\Controllers\ResultChainController::class, 'updatePillarProgramOutputIndicator'])->name('program-output-indicators.update');
        Route::delete('/program-output-indicators/{pillarProgramOutputIndicator}', [\App\Http\Controllers\ResultChainController::class, 'destroyPillarProgramOutputIndicator'])->name('program-output-indicators.destroy');

        // Outcome Indicators
        Route::get('/outcome-indicators', [\App\Http\Controllers\ResultChainController::class, 'outcomeIndicators'])->name('outcome-indicators.index');
        Route::get('/outcome-indicators/create', [\App\Http\Controllers\ResultChainController::class, 'createOutcomeIndicator'])->name('outcome-indicators.create');
        Route::post('/outcome-indicators', [\App\Http\Controllers\ResultChainController::class, 'storeOutcomeIndicator'])->name('outcome-indicators.store');
        Route::get('/outcome-indicators/{outcomeIndicator}/edit', [\App\Http\Controllers\ResultChainController::class, 'editOutcomeIndicator'])->name('outcome-indicators.edit');
        Route::put('/outcome-indicators/{outcomeIndicator}', [\App\Http\Controllers\ResultChainController::class, 'updateOutcomeIndicator'])->name('outcome-indicators.update');
        Route::delete('/outcome-indicators/{outcomeIndicator}', [\App\Http\Controllers\ResultChainController::class, 'destroyOutcomeIndicator'])->name('outcome-indicators.destroy');

        // Impact Indicators
        Route::get('/impact-indicators', [\App\Http\Controllers\ResultChainController::class, 'impactIndicators'])->name('impact-indicators.index');
        Route::get('/impact-indicators/create', [\App\Http\Controllers\ResultChainController::class, 'createImpactIndicator'])->name('impact-indicators.create');
        Route::post('/impact-indicators', [\App\Http\Controllers\ResultChainController::class, 'storeImpactIndicator'])->name('impact-indicators.store');
        Route::get('/impact-indicators/{impactIndicator}/edit', [\App\Http\Controllers\ResultChainController::class, 'editImpactIndicator'])->name('impact-indicators.edit');
        Route::put('/impact-indicators/{impactIndicator}', [\App\Http\Controllers\ResultChainController::class, 'updateImpactIndicator'])->name('impact-indicators.update');
        Route::delete('/impact-indicators/{impactIndicator}', [\App\Http\Controllers\ResultChainController::class, 'destroyImpactIndicator'])->name('impact-indicators.destroy');

        // Disaggregation quick-add (JSON endpoint used by indicator forms)
        Route::post('/disagregation-items/quick-add', [\App\Http\Controllers\ResultChainController::class, 'quickAddDisagregationItem'])->name('disagregation-items.quick-add');
    });

    // Enumeration Records Routes
    Route::prefix('enumerations')->name('enumerations.')->group(function () {
        Route::get('/', [EnumerationController::class, 'index'])->name('index');
        Route::get('/create/{formType}', [EnumerationController::class, 'create'])->name('create');
        Route::post('/{formType}', [EnumerationController::class, 'store'])->name('store');
        Route::get('/record/{enumerationRecord}', [EnumerationController::class, 'show'])->name('show');
        Route::put('/record/{enumerationRecord}/sync-status', [EnumerationController::class, 'updateSyncStatus'])->name('sync-status.update');
        Route::delete('/record/{enumerationRecord}', [EnumerationController::class, 'destroy'])->name('destroy');
        Route::get('/export/{format?}', [EnumerationController::class, 'export'])->name('export');
    });
});







Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return 'Cache cleared!';
});

Route::get('/run-seed', function () {
    Artisan::call('db:seed');
    $output = Artisan::output();

    return response()->json([
        'message' => 'Database seeding executed successfully!',
        'output'  => $output,
    ]);
});

Route::get('/run-worker', function () {
    try {
        Artisan::call('queue:work', [
                               // '--once' => true, // Only process one job
            '--quiet' => true, // Optional: suppress verbose output
        ]);

        $output = Artisan::output();

        return response()->json([
            'status' => 'success',
            'output' => $output,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// Test routes for WebSocket features
Route::middleware(['auth.web'])->group(function () {
    // Test notification
    Route::get('/test-notification', function () {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        
        event(new \App\Events\UserNotification($user->id, 'Test notification from web route'));
        return response()->json(['status' => 'sent']);
    });

    // Test dashboard stats update
    Route::get('/test-dashboard-stats', function () {
        event(new \App\Events\DashboardStatsUpdated([
            'recordsSaved' => rand(10000, 20000),
            'totalUsers' => rand(100, 500),
            'dataPendingSync' => rand(1000, 5000)
        ]));
        return response()->json(['status' => 'sent']);
    });
});
