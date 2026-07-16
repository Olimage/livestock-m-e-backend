<?php

namespace Tests\Feature\IndicatorReporting;

use App\Enums\ReportStatus;
use App\Models\Department;
use App\Models\IndicatorReport;
use App\Models\OutputIndicator;
use App\Models\ReportingPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

class IndicatorDataApiTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    public function test_only_approved_reports_are_exposed(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $user = User::create(['full_name' => 'A', 'email' => 'a@x.io', 'password' => 'secret123']);

        $common = [
            'indicator_type' => OutputIndicator::class, 'indicator_id' => $indicator->id,
            'indicator_code' => $indicator->code, 'department_id' => $dept->id,
            'reporting_period_id' => $period->id, 'created_by' => $user->id, 'actual_value' => 80,
        ];
        IndicatorReport::create($common + ['status' => ReportStatus::Approved, 'published_at' => now()]);
        IndicatorReport::create($common + ['status' => ReportStatus::Pending]);

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/indicator-data?department_id='.$dept->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.data');
    }
}
