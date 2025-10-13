<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/check-host', function (\Illuminate\Http\Request $request) {
    return [
        'host' => $request->getHost(),
        'app_url' => config('app.url')
    ];
});


Route::domain('fmld-baseline.olimageserver.com')->group(function () {


    Route::get('/', function () {

        return Inertia::render('Baseline/Pages/Dashboard', [
            // 'users' => User::all()
        ]);
        // return Inertia::render('Baseline/Index');
        // return Inertia::render('Baseline/Index', [
        //     'users' => User::all()
        // ]);
    })->name('baseline');

    Route::get('/new', function () {
        return Inertia::render('Baseline/Pages/NewEnumeration');
    })->name('new');

    Route::get('/saved-data', function () {
        // return Inertia::render('SavedData');
    })->name('saved-data');


});

Route::get('/', function () {
    // return view('welcome');

    //  return Inertia::render('Welcome');
    //  return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);

    return response()->json('hello world', 200);
})->name('home');

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
