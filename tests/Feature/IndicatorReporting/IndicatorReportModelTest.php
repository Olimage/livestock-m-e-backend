<?php

namespace Tests\Feature\IndicatorReporting;

use App\Enums\ReportStatus;
use App\Models\Department;
use App\Models\IndicatorReport;
use App\Models\OutputIndicator;
use App\Models\ReportingPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicatorReportModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_gets_uuid_casts_status_and_resolves_polymorphic_indicator(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $user = User::create(['full_name' => 'Dir', 'email' => 'd@x.io', 'password' => 'secret123']);
        $period = ReportingPeriod::create(['name' => 'Q1 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $indicator = OutputIndicator::create(['title' => 'Vaccinations', 'department_id' => $dept->id, 'measurement_unit' => 'count']);

        $report = IndicatorReport::create([
            'indicator_type' => OutputIndicator::class,
            'indicator_id' => $indicator->id,
            'indicator_code' => $indicator->code,
            'department_id' => $dept->id,
            'reporting_period_id' => $period->id,
            'status' => ReportStatus::Draft,
            'created_by' => $user->id,
        ]);

        $this->assertNotEmpty($report->uuid);
        $this->assertInstanceOf(ReportStatus::class, $report->fresh()->status);
        $this->assertTrue($report->indicator->is($indicator));
    }
}
