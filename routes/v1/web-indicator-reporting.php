<?php

use App\Http\Controllers\ApprovalWorkflowWebController;
use App\Http\Controllers\IndicatorReportingController;
use App\Http\Controllers\ReportingConfigController;
use App\Http\Controllers\ReportReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('indicator-reporting')->name('indicator-reporting.')->group(function () {
    // Reporting
    Route::get('/reports', [IndicatorReportingController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [IndicatorReportingController::class, 'create'])->name('reports.create');
    Route::post('/reports', [IndicatorReportingController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}/edit', [IndicatorReportingController::class, 'edit'])->name('reports.edit');
    Route::get('/reports/{report}', [IndicatorReportingController::class, 'show'])->name('reports.show');
    Route::put('/reports/{report}', [IndicatorReportingController::class, 'update'])->name('reports.update');
    Route::post('/reports/{report}/submit', [IndicatorReportingController::class, 'submit'])->name('reports.submit');
    Route::post('/reports/{report}/proofs', [IndicatorReportingController::class, 'uploadProof'])->name('reports.proofs.store');
    Route::delete('/reports/{report}/proofs/{proof}', [IndicatorReportingController::class, 'deleteProof'])->name('reports.proofs.destroy');

    // Review queue (PRS / PS)
    Route::get('/review', [ReportReviewController::class, 'queue'])->name('review.queue');
    Route::post('/review/{report}/approve', [ReportReviewController::class, 'approve'])->name('review.approve');
    Route::post('/review/{report}/decline', [ReportReviewController::class, 'decline'])->name('review.decline');

    // Admin: workflow builder
    Route::get('/workflows', [ApprovalWorkflowWebController::class, 'index'])->name('workflows.index');
    Route::get('/workflows/create', [ApprovalWorkflowWebController::class, 'create'])->name('workflows.create');
    Route::post('/workflows', [ApprovalWorkflowWebController::class, 'store'])->name('workflows.store');
    Route::get('/workflows/{workflow}/edit', [ApprovalWorkflowWebController::class, 'edit'])->name('workflows.edit');
    Route::put('/workflows/{workflow}', [ApprovalWorkflowWebController::class, 'update'])->name('workflows.update');
    Route::delete('/workflows/{workflow}', [ApprovalWorkflowWebController::class, 'destroy'])->name('workflows.destroy');

    // Admin: reporting periods + settings
    Route::get('/periods', [ReportingConfigController::class, 'periods'])->name('periods.index');
    Route::post('/periods', [ReportingConfigController::class, 'storePeriod'])->name('periods.store');
    Route::put('/periods/{reportingPeriod}', [ReportingConfigController::class, 'updatePeriod'])->name('periods.update');
    Route::delete('/periods/{reportingPeriod}', [ReportingConfigController::class, 'destroyPeriod'])->name('periods.destroy');
    Route::get('/settings', [ReportingConfigController::class, 'settings'])->name('settings.index');
    Route::put('/settings', [ReportingConfigController::class, 'updateSettings'])->name('settings.update');
});
