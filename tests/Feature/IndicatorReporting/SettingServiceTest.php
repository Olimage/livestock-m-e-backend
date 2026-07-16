<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_boolean_setting_round_trips_as_bool(): void
    {
        Setting::create([
            'key' => SettingService::ALLOW_SUPPORTING_DEPT,
            'value' => '0',
            'type' => 'boolean',
            'label' => 'Allow supporting-department reporting',
        ]);

        $service = app(SettingService::class);
        $this->assertFalse($service->get(SettingService::ALLOW_SUPPORTING_DEPT));

        $service->set(SettingService::ALLOW_SUPPORTING_DEPT, true);
        $this->assertTrue($service->get(SettingService::ALLOW_SUPPORTING_DEPT));
    }

    public function test_get_returns_default_when_missing(): void
    {
        $this->assertSame('fallback', app(SettingService::class)->get('nope', 'fallback'));
    }
}
