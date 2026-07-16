<?php

use App\Http\Controllers\Api\ApprovalTrailController;
use App\Http\Controllers\Api\ApprovalWorkflowController;
use App\Http\Controllers\Api\IndicatorDataController;
use App\Http\Controllers\Api\IndicatorReportController;
use App\Http\Controllers\Api\ReportApprovalController;
use App\Http\Controllers\Api\ReportingPeriodController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['json-response', 'jwt.verify'])->group(function () {
    // Admin: workflow configuration
    Route::apiResource('workflows', ApprovalWorkflowController::class);
    Route::post('workflows/{workflow}/departments', [ApprovalWorkflowController::class, 'assignDepartments'])
        ->name('workflows.departments');

    // Reporting
    Route::get('indicator-reports', [IndicatorReportController::class, 'index']);
    Route::post('indicator-reports', [IndicatorReportController::class, 'store']);
    Route::get('indicator-reports/{report}', [IndicatorReportController::class, 'show']);
    Route::put('indicator-reports/{report}', [IndicatorReportController::class, 'update']);
    Route::post('indicator-reports/{report}/submit', [IndicatorReportController::class, 'submit']);
    Route::post('indicator-reports/{report}/proofs', [IndicatorReportController::class, 'uploadProof']);
    Route::delete('indicator-reports/{report}/proofs/{proof}', [IndicatorReportController::class, 'deleteProof']);

    // Approvals
    Route::post('indicator-reports/{report}/approve', [ReportApprovalController::class, 'approve']);
    Route::post('indicator-reports/{report}/decline', [ReportApprovalController::class, 'decline']);

    // Audit trail
    Route::get('indicator-reports/{report}/trail', [ApprovalTrailController::class, 'forReport']);
    Route::get('approval-trails', [ApprovalTrailController::class, 'index']);

    // Reporting periods + settings
    Route::get('reporting-periods', [ReportingPeriodController::class, 'index']);
    Route::post('reporting-periods', [ReportingPeriodController::class, 'store']);
    Route::put('reporting-periods/{reportingPeriod}', [ReportingPeriodController::class, 'update']);
    Route::delete('reporting-periods/{reportingPeriod}', [ReportingPeriodController::class, 'destroy']);
    Route::get('settings', [SettingController::class, 'index']);
    Route::put('settings', [SettingController::class, 'update']);

    // External integration (read-only, approved data)
    Route::get('indicator-data', [IndicatorDataController::class, 'index']);
    Route::get('indicators/{code}/reports', [IndicatorDataController::class, 'forIndicator']);
});
