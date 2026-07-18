<?php

use Illuminate\Support\Facades\Route;

$namespace = 'App\Http\Controllers';

Route::group([
    'prefix' => '/v1',
    'namespace' => $namespace,
    'middleware' => ['json-response', 'cors'],
], function () {
    require __DIR__.'/v1/auth.php';
    require __DIR__.'/v1/user.php';
    require __DIR__.'/v1/supervisor-enumerator.php';
    require __DIR__.'/v1/indicator-reporting.php';

    Route::prefix('locations')->name('api.locations.')->group(function () {
        Route::get('/zones', [App\Http\Controllers\LocationController::class, 'ApiGetZones'])->name('zones');
        Route::get('/states', [App\Http\Controllers\LocationController::class, 'ApiGetStates'])->name('states');
        Route::get('/lgas', [App\Http\Controllers\LocationController::class, 'ApiGetLgas'])->name('lgas');
    });

    // Protected enumeration submissions (JWT auth)
    Route::group([
        'middleware' => ['json-response', 'jwt.verify'],
    ], function () {
        Route::post('/enumeration-records', 'Api\EnumerationRecordController@store')
            ->name('api.enumerations.records.store');
    });

    // forms

    Route::prefix('forms')->name('forms.')->middleware('jwt.verify')->group(function () {

        Route::prefix('indicator')->controller('IndicatorFormController')->name('indicator.')->group(function () {

            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/fields', 'getIndicatorFormFields')->name('fields');

        });
    });

    // / prgrams

    Route::prefix('programs')->name('programs.')->group(function () {
        Route::prefix('indicators')->name('indicators.')->group(function () {
            Route::get('/', [App\Http\Controllers\ProgramsController::class, 'getIndicators'])->name('list');
        });

        Route::prefix('modules')->name('modules.')->group(function () {
            Route::get('/', [App\Http\Controllers\ProgramsController::class, 'getModules'])->name('list');
        });
        Route::prefix('sectoral-goals')->name('sectoral-goals.')->group(function () {
            Route::get('/', [App\Http\Controllers\ProgramsController::class, 'getSectoralGoals'])->name('list');
        });

    });

    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [App\Http\Controllers\DepartmentController::class, 'getDepartments'])->name('list');
    });

    // Dashboard analytics endpoints (JWT) — see docs/superpowers/specs/2026-07-16-mems-dashboard-api-contract.md
    Route::middleware('jwt.verify')->group(function () {
        Route::get('/bond-deliverables', [App\Http\Controllers\Api\BondDeliverableApiController::class, 'index'])
            ->name('api.bond-deliverables.index');

        Route::get('/sector-outcomes', [App\Http\Controllers\Api\SectorOutcomeApiController::class, 'index'])
            ->name('api.sector-outcomes.index');
        Route::get('/sector-outcomes/impact', [App\Http\Controllers\Api\SectorOutcomeApiController::class, 'impact'])
            ->name('api.sector-outcomes.impact');
        Route::get('/sector-outcomes/{id}/trends', [App\Http\Controllers\Api\SectorOutcomeApiController::class, 'trends'])
            ->whereNumber('id')->name('api.sector-outcomes.trends');

        Route::get('/strategic-programs', [App\Http\Controllers\Api\StrategicProgramApiController::class, 'index'])
            ->name('api.strategic-programs.index');

        Route::get('/dashboard/overview', [App\Http\Controllers\Api\DashboardApiController::class, 'overview'])
            ->name('api.dashboard.overview');
        Route::get('/dashboard/alerts', [App\Http\Controllers\Api\DashboardApiController::class, 'alerts'])
            ->name('api.dashboard.alerts');
        Route::get('/dashboard/status-breakdown', [App\Http\Controllers\Api\DashboardApiController::class, 'statusBreakdown'])
            ->name('api.dashboard.status-breakdown');

        Route::get('/data-health/metrics', [App\Http\Controllers\Api\DataHealthApiController::class, 'metrics'])
            ->name('api.data-health.metrics');
        Route::get('/data-health/validators', [App\Http\Controllers\Api\DataHealthApiController::class, 'validators'])
            ->name('api.data-health.validators');
        Route::get('/data-health/activity-log', [App\Http\Controllers\Api\DataHealthApiController::class, 'activityLog'])
            ->name('api.data-health.activity-log');

        Route::get('/reporting-obligations', [App\Http\Controllers\Api\ReportingObligationApiController::class, 'index'])
            ->name('api.reporting-obligations.index');
        Route::get('/suppressed-indicators', [App\Http\Controllers\Api\ReportingObligationApiController::class, 'suppressed'])
            ->name('api.suppressed-indicators.index');

        Route::get('/result-chain', [App\Http\Controllers\Api\ResultChainApiController::class, 'index'])
            ->name('api.result-chain.index');

        Route::get('/sector-trends', [App\Http\Controllers\Api\SectorTrendApiController::class, 'index'])
            ->name('api.sector-trends.index');

        Route::get('/sector-map', [App\Http\Controllers\Api\SectorMapApiController::class, 'index'])
            ->name('api.sector-map.index');

        Route::get('/validation-pipeline', [App\Http\Controllers\Api\ValidationPipelineApiController::class, 'index'])
            ->name('api.validation-pipeline.index');
    });

    // Activity logs routes
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('index');
        Route::get('/my-activity', [App\Http\Controllers\ActivityLogController::class, 'myActivity'])->name('my-activity');
        Route::get('/statistics', [App\Http\Controllers\ActivityLogController::class, 'statistics'])->name('statistics');
        Route::get('/{id}', [App\Http\Controllers\ActivityLogController::class, 'show'])->name('show');
        Route::delete('/cleanup', [App\Http\Controllers\ActivityLogController::class, 'cleanup'])->name('cleanup');
    });

});

// Dashboard API endpoints (session auth required)
Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    'api.session.auth',
])->group(function () {
    Route::get('/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'getStats'])->name('api.dashboard.stats');
});

// NOTE: the former `app-setup` routes referenced App\Http\Controllers\AppSetupController,
// a class that does not exist — they always 500'd and broke `php artisan route:list`.
// Removed as dead code (no callers anywhere in mems or the frontends). If app-setup
// endpoints are needed later, add the controller first, then the routes.
