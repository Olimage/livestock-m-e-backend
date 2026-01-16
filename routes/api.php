<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$namespace = 'App\Http\Controllers';

Route::group([
    'prefix' => '/v1',
    'namespace' => $namespace,
    'middleware' => ['json-response', 'cors'],
], function () {
    require __DIR__ . '/v1/auth.php';
    require __DIR__ . '/v1/user.php';

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



    /// prgrams

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
    Route::prefix('presidential-priorities')->name('presidential-priorities.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProgramsController::class, 'getPresidentialPriorities'])->name('list');
    });

    Route::prefix('bond-outcomes')->name('bond-outcomes.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProgramsController::class, 'getBondOutcomes'])->name('list');
    });
});



});

// Dashboard API endpoints (session auth required)
Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    'api.session.auth'
])->group(function () {
    Route::get('/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'getStats'])->name('api.dashboard.stats');
});

Route::prefix('app-setup')->group(function () {
    Route::get('/departments', [App\Http\Controllers\AppSetupController::class, 'getDepartments'])->name('api.app-setup.departments');
    Route::get('/sectors', [App\Http\Controllers\AppSetupController::class, 'getSectors'])->name('api.app-setup.sectors');

});



