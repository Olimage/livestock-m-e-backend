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
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Supervisor-Enumerator Management
    require __DIR__.'/v1/web-supervisor-enumerator.php';

    // Return immediate children of a department (used by cascading selectors)
    Route::get('/departments/{id}/children', function ($id) {
        $children = Department::where('parent_id', $id)->orderBy('name')->get();
        return response()->json($children);
    })->name('departments.children');

    // Lightweight broadcast/test endpoints (auth required)
    Route::get('/broadcast/live', [BroadcastController::class, 'sendLiveData'])->name('broadcast.live');
    Route::get('/broadcast/notify/{user}', [BroadcastController::class, 'notifyUser'])->name('broadcast.notify');
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
