<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\ReportingPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingPeriodModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_scope_filters_closed_periods(): void
    {
        ReportingPeriod::create(['name' => 'Q1 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1, 'is_open' => true]);
        ReportingPeriod::create(['name' => 'Q2 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 2, 'is_open' => false]);

        $this->assertSame(1, ReportingPeriod::open()->count());
        $this->assertTrue(ReportingPeriod::first()->is_open);
    }
}
