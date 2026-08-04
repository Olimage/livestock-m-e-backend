<?php

use App\Http\Controllers\Api\GeographyApiController;
use App\Http\Controllers\Api\ReferenceDataApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.verify', 'permission:manage-settings'])
    ->prefix('locations')
    ->name('api.locations.')
    ->group(function () {
        Route::post('zones', [GeographyApiController::class, 'storeZone'])->name('zones.store');
        Route::put('zones/{zone}', [GeographyApiController::class, 'updateZone'])->name('zones.update');
        Route::delete('zones/{zone}', [GeographyApiController::class, 'destroyZone'])->name('zones.destroy');

        Route::post('states', [GeographyApiController::class, 'storeState'])->name('states.store');
        Route::put('states/{state}', [GeographyApiController::class, 'updateState'])->name('states.update');
        Route::delete('states/{state}', [GeographyApiController::class, 'destroyState'])->name('states.destroy');

        Route::post('lgas', [GeographyApiController::class, 'storeLga'])->name('lgas.store');
        Route::put('lgas/{lga}', [GeographyApiController::class, 'updateLga'])->name('lgas.update');
        Route::delete('lgas/{lga}', [GeographyApiController::class, 'destroyLga'])->name('lgas.destroy');
    });

Route::middleware(['jwt.verify', 'permission:manage-settings'])
    ->name('api.reference-data.')
    ->group(function () {
        Route::get('nlgas-pillars', [ReferenceDataApiController::class, 'indexNlgasPillars'])->name('nlgas-pillars.index');
        Route::post('nlgas-pillars', [ReferenceDataApiController::class, 'storeNlgasPillar'])->name('nlgas-pillars.store');
        Route::put('nlgas-pillars/{pillar}', [ReferenceDataApiController::class, 'updateNlgasPillar'])->name('nlgas-pillars.update');
        Route::delete('nlgas-pillars/{pillar}', [ReferenceDataApiController::class, 'destroyNlgasPillar'])->name('nlgas-pillars.destroy');

        Route::get('sectoral-goals', [ReferenceDataApiController::class, 'indexSectoralGoals'])->name('sectoral-goals.index');
        Route::post('sectoral-goals', [ReferenceDataApiController::class, 'storeSectoralGoal'])->name('sectoral-goals.store');
        Route::put('sectoral-goals/{goal}', [ReferenceDataApiController::class, 'updateSectoralGoal'])->name('sectoral-goals.update');
        Route::delete('sectoral-goals/{goal}', [ReferenceDataApiController::class, 'destroySectoralGoal'])->name('sectoral-goals.destroy');

        Route::get('disaggregation-categories', [ReferenceDataApiController::class, 'indexDisagregationCategories'])->name('disaggregation-categories.index');
        Route::post('disaggregation-categories', [ReferenceDataApiController::class, 'storeDisagregationCategory'])->name('disaggregation-categories.store');
        Route::put('disaggregation-categories/{category}', [ReferenceDataApiController::class, 'updateDisagregationCategory'])->name('disaggregation-categories.update');
        Route::delete('disaggregation-categories/{category}', [ReferenceDataApiController::class, 'destroyDisagregationCategory'])->name('disaggregation-categories.destroy');

        Route::post('disaggregation-categories/{category}/items', [ReferenceDataApiController::class, 'storeDisagregationItem'])->name('disaggregation-categories.items.store');
        Route::put('disaggregation-categories/{category}/items/{item}', [ReferenceDataApiController::class, 'updateDisagregationItem'])->name('disaggregation-categories.items.update')->scopeBindings();
        Route::delete('disaggregation-categories/{category}/items/{item}', [ReferenceDataApiController::class, 'destroyDisagregationItem'])->name('disaggregation-categories.items.destroy')->scopeBindings();
    });
