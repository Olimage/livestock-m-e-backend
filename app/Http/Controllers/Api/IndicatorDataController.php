<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\IndicatorReport;
use Illuminate\Http\Request;

class IndicatorDataController extends Controller
{
    public function index(Request $request)
    {
        $query = IndicatorReport::query()
            ->where('status', ReportStatus::Approved->value)
            ->with(['period:id,name,type,year,period_number', 'department:id,name', 'values'])
            ->when($request->get('indicator_code'), fn ($q, $c) => $q->where('indicator_code', $c))
            ->when($request->get('department_id'), fn ($q, $d) => $q->where('department_id', $d))
            ->when($request->get('reporting_period_id'), fn ($q, $p) => $q->where('reporting_period_id', $p))
            ->when($request->get('year'), fn ($q, $y) => $q->whereHas('period', fn ($p) => $p->where('year', $y)))
            ->latest('published_at');

        return response()->json(['success' => true, 'data' => $query->paginate($request->get('per_page', 50))]);
    }

    public function forIndicator(Request $request, string $code)
    {
        $reports = IndicatorReport::where('status', ReportStatus::Approved->value)
            ->where('indicator_code', $code)
            ->with(['period:id,name,year,period_number', 'values'])
            ->orderBy('reporting_period_id')
            ->get();

        return response()->json(['success' => true, 'data' => $reports]);
    }
}
