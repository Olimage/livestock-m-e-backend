<?php

use App\Http\Controllers\SupervisorEnumeratorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.web'])->group(function () {
    Route::get('/supervisor-enumerators', [SupervisorEnumeratorController::class, 'webIndex'])
        ->name('supervisor-enumerators.index');
        
    Route::get('/supervisor-enumerators/create', [SupervisorEnumeratorController::class, 'webCreate'])
        ->name('supervisor-enumerators.create');

    Route::post('/supervisor-enumerator/assign', [SupervisorEnumeratorController::class, 'assign'])
        ->name('supervisor-enumerators.assign');

    // Use URL parameters for delete to ensure IDs are received reliably
    Route::delete('/supervisor-enumerator/remove/{supervisor}/{enumerator}', [SupervisorEnumeratorController::class, 'remove'])
        ->name('supervisor-enumerators.remove');
});