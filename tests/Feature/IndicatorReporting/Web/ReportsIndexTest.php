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

class ReportsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_their_reports_on_index(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $me = User::create(['full_name' => 'Me', 'email' => 'me@x.io', 'password' => 'secret123']);
        $other = User::create(['full_name' => 'Other', 'email' => 'o@x.io', 'password' => 'secret123']);

        $base = [
            'indicator_type' => OutputIndicator::class, 'indicator_id' => $indicator->id,
            'indicator_code' => $indicator->code, 'department_id' => $dept->id,
            'reporting_period_id' => $period->id, 'status' => ReportStatus::Draft,
        ];
        IndicatorReport::create($base + ['created_by' => $me->id]);
        IndicatorReport::create($base + ['created_by' => $other->id]);

        $this->actingAs($me)
            ->get(route('indicator-reporting.reports.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('IndicatorReporting/Reports/Index')
                ->has('reports.data', 1)
                ->where('can.viewAll', false)
            );
    }
}
