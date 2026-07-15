<?php

namespace Tests\Feature;

use App\Models\BondDeliverable;
use App\Models\BondOutputIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BondDeliverableIndicatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_bond_deliverable_syncs_bond_output_indicators(): void
    {
        $boi = BondOutputIndicator::create(['title' => 'Bond A']);
        $bd = BondDeliverable::create(['code' => 'BD-1', 'deliverable' => 'Do X']);

        $bd->bondOutputIndicators()->sync([$boi->id]);

        $this->assertTrue(
            $bd->bondOutputIndicators()->where('bond_output_indicators.id', $boi->id)->exists()
        );
    }
}
