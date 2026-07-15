<?php

namespace Tests\Feature;

use App\Models\ImpactIndicator;
use App\Models\IndicatorBaselineYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaselinePolymorphicTest extends TestCase
{
    use RefreshDatabase;

    public function test_baseline_attaches_to_a_result_chain_indicator(): void
    {
        $impact = ImpactIndicator::create(['title' => 'Impact A', 'department_id' => null]);

        $baseline = IndicatorBaselineYear::create([
            'indicatorable_id'   => $impact->id,
            'indicatorable_type' => ImpactIndicator::class,
            'baseline_year'      => 2024,
            'target_year'        => 2027,
            'baseline'           => 10,
            'target'             => 50,
            'actual'             => 20,
        ]);

        $this->assertInstanceOf(ImpactIndicator::class, $baseline->indicatorable);
        $this->assertEquals($impact->id, $baseline->indicatorable->id);
    }
}
