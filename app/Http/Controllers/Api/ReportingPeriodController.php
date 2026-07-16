<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportingPeriod;
use Illuminate\Http\Request;

class ReportingPeriodController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportingPeriod::query()->orderByDesc('year')->orderByDesc('period_number');
        if ($request->boolean('open_only')) {
            $query->open();
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $this->authorizeManage($request);
        $data = $this->validated($request);

        return response()->json(['success' => true, 'message' => 'Period created.', 'data' => ReportingPeriod::create($data)], 201);
    }

    public function update(Request $request, ReportingPeriod $reportingPeriod)
    {
        $this->authorizeManage($request);
        $reportingPeriod->update($this->validated($request, false));

        return response()->json(['success' => true, 'message' => 'Period updated.', 'data' => $reportingPeriod]);
    }

    public function destroy(Request $request, ReportingPeriod $reportingPeriod)
    {
        $this->authorizeManage($request);
        $reportingPeriod->delete();

        return response()->json(['success' => true, 'message' => 'Period deleted.']);
    }

    private function authorizeManage(Request $request): void
    {
        $user = $request->user();
        abort_unless($user->is_admin || $user->hasPermission('manage-reporting-periods'), 403, 'Forbidden.');
    }

    private function validated(Request $request, bool $required = true): array
    {
        $rule = $required ? 'required' : 'sometimes';

        return $request->validate([
            'name' => "$rule|string|max:255",
            'type' => "$rule|in:month,quarter,year",
            'year' => "$rule|integer|min:2000|max:2100",
            'period_number' => 'nullable|integer|min:1|max:12',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_open' => 'boolean',
        ]);
    }
}
