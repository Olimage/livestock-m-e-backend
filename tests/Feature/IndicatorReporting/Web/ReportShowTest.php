<?php

namespace Tests\Feature\IndicatorReporting\Web;

use App\Enums\ReportStatus;
use App\Models\Department;
use App\Models\IndicatorReport;
use App\Models\OutputIndicator;
use App\Models\ReportingPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_report_detail(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $me = User::create(['full_name' => 'Me', 'email' => 'me@x.io', 'password' => 'secret123']);
        $report = IndicatorReport::create([
            'indicator_type' => OutputIndicator::class, 'indicator_id' => $indicator->id,
            'indicator_code' => $indicator->code, 'department_id' => $dept->id,
            'reporting_period_id' => $period->id, 'status' => ReportStatus::Draft, 'created_by' => $me->id,
        ]);

        $this->actingAs($me)
            ->get(route('indicator-reporting.reports.show', $report->uuid))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('IndicatorReporting/Reports/Show')
                ->where('report.uuid', $report->uuid)
                ->where('can.edit', true)
            );
    }
}
