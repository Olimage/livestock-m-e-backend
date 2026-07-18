<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\BondOutputIndicator;
use App\Models\ImpactIndicator;
use App\Models\IndicatorReport;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;

/**
 * GET /api/v1/validation-pipeline — submitted indicator reports shaped for the
 * Data Validation Pipeline table (the report review workflow).
 */
class ValidationPipelineApiController extends Controller
{
    private const TYPE_LABEL = [
        OutputIndicator::class => 'Output Indicator (OPT)',
        OutcomeIndicator::class => 'Outcome Indicator (OCI)',
        ImpactIndicator::class => 'Impact Indicator (IMP)',
        BondOutputIndicator::class => 'Bond Output Indicator (BOI)',
    ];

    public function index()
    {
        try {
            $reports = IndicatorReport::with(['indicator', 'department'])
                ->whereNotNull('submitted_at')
                ->orderByDesc('submitted_at')
                ->limit(50)
                ->get();

            $data = $reports->map(function (IndicatorReport $r) {
                $daysPending = $r->submitted_at ? (int) $r->submitted_at->diffInDays(now()) : 0;

                return [
                    'id' => $r->id,
                    'indicator' => $r->indicator?->title ?? $r->indicator_code ?? "Indicator {$r->indicator_id}",
                    'owner' => $r->department?->name ?? '—',
                    'submissionDate' => $r->submitted_at?->toDateString(),
                    'stage' => self::TYPE_LABEL[$r->indicator_type] ?? 'Indicator',
                    'daysPending' => "{$daysPending} Days",
                    'slaStatus' => 90,
                    'action' => $this->action($r->status),
                ];
            });

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve validation pipeline',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function action(ReportStatus $status): string
    {
        return match ($status) {
            ReportStatus::Approved => 'Approved',
            ReportStatus::Returned => 'Rejected',
            ReportStatus::Pending => 'Approved',
            ReportStatus::Draft => 'Suppressed',
        };
    }
}
