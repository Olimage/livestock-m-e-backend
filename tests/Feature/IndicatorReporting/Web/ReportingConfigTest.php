<?php

namespace Tests\Feature\IndicatorReporting\Web;

use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportingConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_period_and_updates_setting(): void
    {
        Setting::create(['key' => SettingService::ALLOW_SUPPORTING_DEPT, 'value' => '0', 'type' => 'boolean', 'label' => 'Allow']);
        $admin = User::create(['full_name' => 'A', 'email' => 'a@x.io', 'password' => 'secret123', 'is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('indicator-reporting.periods.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('IndicatorReporting/Config/Periods'));

        $this->actingAs($admin)->post(route('indicator-reporting.periods.store'), [
            'name' => 'Q3 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 3, 'is_open' => true,
        ])->assertRedirect()->assertSessionHas('success');

        $this->actingAs($admin)->put(route('indicator-reporting.settings.update'), [
            'settings' => [SettingService::ALLOW_SUPPORTING_DEPT => true],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertTrue(app(SettingService::class)->get(SettingService::ALLOW_SUPPORTING_DEPT));
    }
}
