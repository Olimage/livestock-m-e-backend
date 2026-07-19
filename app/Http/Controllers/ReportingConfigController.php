<?php

namespace App\Http\Controllers;

use App\Models\ReportingPeriod;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReportingConfigController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function periods(Request $request)
    {
        $this->guardPeriods($request);

        return Inertia::render('IndicatorReporting/Config/Periods', [
            'periods' => ReportingPeriod::orderByDesc('year')->orderByDesc('period_number')->get(),
        ]);
    }

    public function storePeriod(Request $request)
    {
        $this->guardPeriods($request);
        ReportingPeriod::create($this->validatedPeriod($request));

        return back()->with('success', 'Period created.');
    }

    public function updatePeriod(Request $request, ReportingPeriod $reportingPeriod)
    {
        $this->guardPeriods($request);
        $reportingPeriod->update($this->validatedPeriod($request, $reportingPeriod->id));

        return back()->with('success', 'Period updated.');
    }

    public function destroyPeriod(Request $request, ReportingPeriod $reportingPeriod)
    {
        $this->guardPeriods($request);
        $reportingPeriod->delete();

        return back()->with('success', 'Period deleted.');
    }

    public function settings(Request $request)
    {
        $this->guardSettings($request);

        return Inertia::render('IndicatorReporting/Config/Settings', [
            'settings' => $this->settings->allPublic(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $this->guardSettings($request);
        $data = $request->validate(['settings' => 'required|array']);
        foreach ($data['settings'] as $key => $value) {
            $this->settings->set($key, $value);
        }

        return back()->with('success', 'Settings updated.');
    }

    private function guardPeriods(Request $request): void
    {
        abort_unless($request->user()->is_admin || $request->user()->hasPermission('manage-reporting-periods'), 403);
    }

    private function guardSettings(Request $request): void
    {
        abort_unless($request->user()->is_admin || $request->user()->hasPermission('manage-settings'), 403);
    }

    private function validatedPeriod(Request $request, ?int $ignoreId = null): array
    {
        // A quarter has 4 periods, a month 12, a year none.
        $maxPeriod = $request->input('type') === 'month' ? 12 : 4;

        return $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:month,quarter,year',
            'year' => 'required|integer|min:2000|max:2100',
            'period_number' => [
                'nullable', 'integer', 'min:1', "max:{$maxPeriod}",
                Rule::unique('reporting_periods')
                    ->where(fn ($q) => $q->where('type', $request->input('type'))->where('year', $request->input('year')))
                    ->ignore($ignoreId),
            ],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_open' => 'boolean',
        ], [
            'period_number.unique' => 'That period already exists for the selected type and year.',
            'period_number.max' => 'The period number is too high for the selected type (max 4 for quarters, 12 for months).',
        ]);
    }
}
