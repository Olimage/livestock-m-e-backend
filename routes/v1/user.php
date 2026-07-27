<?php

use Illuminate\Support\Facades\Route;

// unproctected routes

Route::group([
    'prefix' => '/user',
    'namespace' => 'User',
], function () {

    Route::get('/home', function () {
        return 123;
    });

});

// protected routes
Route::group([
    'prefix' => '/user',
    'namespace' => 'User',
    'middleware' => ['json-response', 'jwt.verify'],
], function () {

    // Casing matters: the class is UserController. A lowercase 'c' resolves on
    // a case-insensitive filesystem (Windows dev) but 500s on the Linux server.
    Route::prefix('/profile')->controller('UserController')->group(function () {

        Route::get('/', 'getUserProfile');
    });

});
