<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

class ReportingPeriodApiTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    public function test_admin_creates_period_and_any_user_lists(): void
    {
        $admin = User::create(['full_name' => 'A', 'email' => 'a@x.io', 'password' => 'secret123', 'is_admin' => true]);
        $user = User::create(['full_name' => 'U', 'email' => 'u@x.io', 'password' => 'secret123']);

        $this->withHeaders($this->authHeaders($admin))->postJson('/api/v1/reporting-periods', [
            'name' => 'Q3 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 3, 'is_open' => true,
        ])->assertCreated();

        $this->withHeaders($this->authHeaders($user))->getJson('/api/v1/reporting-periods')
            ->assertOk()->assertJsonPath('success', true);
    }

    public function test_non_admin_cannot_create_period(): void
    {
        $user = User::create(['full_name' => 'U', 'email' => 'u@x.io', 'password' => 'secret123']);
        $this->withHeaders($this->authHeaders($user))->postJson('/api/v1/reporting-periods', [
            'name' => 'Q3 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 3,
        ])->assertForbidden();
    }
}
