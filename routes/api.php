<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


$namespace = 'App\Http\Controllers';

Route::group([
    'prefix'=>'/v1',
    'namespace' => $namespace,
    'middleware' => ['json-response', 'cors'],
], function(){


    require __DIR__ . '/v1/auth.php';
    require __DIR__ . '/v1/user.php';

});

// Dashboard API endpoints (session auth required)
Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    'api.session.auth'
])->group(function () {
    Route::get('/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'getStats'])->name('api.dashboard.stats');
});



