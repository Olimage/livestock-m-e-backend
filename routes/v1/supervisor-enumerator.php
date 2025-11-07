<?php

use App\Http\Controllers\SupervisorEnumeratorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::get('/supervisor-enumerators', [SupervisorEnumeratorController::class, 'index'])
        ->name('supervisor-enumerators.index');
        
    Route::post('/supervisor-enumerators/assign', [SupervisorEnumeratorController::class, 'assign'])
        ->name('supervisor-enumerators.assign');
        
    Route::delete('/supervisor-enumerators/{supervisor}/{enumerator}', [SupervisorEnumeratorController::class, 'remove'])
        ->name('supervisor-enumerators.remove');
        
    Route::get('/supervisor-enumerators/{supervisor}/enumerators', [SupervisorEnumeratorController::class, 'getEnumerators'])
        ->name('supervisor-enumerators.get-enumerators');
});