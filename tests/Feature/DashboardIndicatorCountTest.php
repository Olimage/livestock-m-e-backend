<?php

namespace Tests\Feature;

use App\Models\ImpactIndicator;
use App\Models\OutputIndicator;
use App\Support\ResultChainIndicators;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardIndicatorCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_indicator_count_sums_result_chain_types(): void
    {
        OutputIndicator::create(['title' => 'O1']);
        ImpactIndicator::create(['title' => 'I1']);

        $total = collect(ResultChainIndicators::TYPES)->keys()
            ->sum(fn ($class) => $class::count());

        $this->assertEquals(2, $total);
    }
}
