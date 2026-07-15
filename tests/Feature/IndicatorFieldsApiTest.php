<?php

namespace Tests\Feature;

use App\Http\Controllers\IndicatorFormController;
use App\Models\ImpactIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class IndicatorFieldsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_fields_endpoint_returns_result_chain_indicators(): void
    {
        $impact = ImpactIndicator::create(['title' => 'Impact A', 'measurement_unit' => '%']);

        $response = (new IndicatorFormController)->getIndicatorFormFields(new Request);
        $payload = $response->getData(true);

        $this->assertTrue($payload['success']);
        $codes = array_column($payload['data'], 'code');
        $this->assertContains($impact->code, $codes);
    }
}
