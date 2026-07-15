<?php

namespace Tests\Feature;

use App\Models\Lga;
use App\Models\State;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeographyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::create(['full_name' => 'Ad', 'email' => uniqid().'@x.io', 'password' => bcrypt('x'), 'is_admin' => true]);
    }

    public function test_admin_can_create_zone(): void
    {
        $this->actingAs($this->admin())->post('/settings/zones', ['name' => 'North West', 'code' => 'NW'])
            ->assertRedirect();

        $this->assertTrue(Zone::where('name', 'North West')->exists());
    }

    public function test_deleting_zone_with_states_is_rejected(): void
    {
        $zone = Zone::create(['name' => 'NW', 'code' => 'NW']);
        State::create(['name' => 'Kano', 'zone_id' => $zone->id]);

        $this->actingAs($this->admin())->delete("/settings/zones/{$zone->id}");

        $this->assertTrue(Zone::whereKey($zone->id)->exists());
    }

    public function test_admin_can_create_state_under_zone(): void
    {
        $zone = Zone::create(['name' => 'NW', 'code' => 'NW']);

        $this->actingAs($this->admin())->post('/settings/states', ['name' => 'Kano', 'zone_id' => $zone->id])
            ->assertRedirect();

        $this->assertTrue(State::where(['name' => 'Kano', 'zone_id' => $zone->id])->exists());
    }

    public function test_admin_can_create_lga_under_state(): void
    {
        $zone = Zone::create(['name' => 'NW', 'code' => 'NW']);
        $state = State::create(['name' => 'Kano', 'zone_id' => $zone->id]);

        $this->actingAs($this->admin())->post('/settings/lgas', ['name' => 'Nassarawa', 'state_id' => $state->id])
            ->assertRedirect();

        $this->assertTrue(Lga::where(['name' => 'Nassarawa', 'state_id' => $state->id])->exists());
    }

    public function test_geography_menu_shows_for_admin(): void
    {
        \Illuminate\Support\Facades\Auth::login($this->admin());
        $nav = collect(\App\Services\NavigationService::getNavigation())->pluck('name');
        \Illuminate\Support\Facades\Auth::logout();

        $this->assertTrue($nav->contains('Geography'));
    }
}
