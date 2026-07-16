<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

class SettingApiTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    public function test_admin_toggles_supporting_department_setting(): void
    {
        Setting::create(['key' => SettingService::ALLOW_SUPPORTING_DEPT, 'value' => '0', 'type' => 'boolean', 'label' => 'Allow']);
        $admin = User::create(['full_name' => 'A', 'email' => 'a@x.io', 'password' => 'secret123', 'is_admin' => true]);

        $this->withHeaders($this->authHeaders($admin))->putJson('/api/v1/settings', [
            'settings' => [SettingService::ALLOW_SUPPORTING_DEPT => true],
        ])->assertOk();

        $this->assertTrue(app(SettingService::class)->get(SettingService::ALLOW_SUPPORTING_DEPT));
    }
}
