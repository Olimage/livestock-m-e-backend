<?php

namespace Tests\Feature;

use App\Models\Lga;
use App\Models\State;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class GeographyApiWriteTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'full_name' => 'Admin',
            'email' => uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'is_admin' => true,
        ]);
    }

    private function plainUser(): User
    {
        return User::create([
            'full_name' => 'Viewer',
            'email' => uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'is_admin' => false,
        ]);
    }

    private function headers(User $user): array
    {
        return ['Authorization' => 'Bearer '.JWTAuth::fromUser($user)];
    }

    public function test_writes_require_authentication(): void
    {
        $this->postJson('/api/v1/locations/zones', ['name' => 'X'])->assertStatus(401);
    }

    public function test_writes_require_the_manage_settings_permission(): void
    {
        $this->withHeaders($this->headers($this->plainUser()))
            ->postJson('/api/v1/locations/zones', ['name' => 'South East', 'code' => 'SE'])
            ->assertStatus(403);

        $this->assertFalse(Zone::where('name', 'South East')->exists());
    }

    public function test_admin_can_create_update_and_delete_a_zone(): void
    {
        $admin = $this->admin();

        $created = $this->withHeaders($this->headers($admin))
            ->postJson('/api/v1/locations/zones', ['name' => 'South East', 'code' => 'SE'])
            ->assertStatus(201)
            ->assertJsonPath('status', true)
            ->json('data');

        $this->assertSame('South East', $created['name']);
        $this->assertNotEmpty($created['uuid']);

        $this->withHeaders($this->headers($admin))
            ->putJson("/api/v1/locations/zones/{$created['id']}", ['name' => 'South-East', 'code' => 'SE'])
            ->assertOk();

        $this->assertSame('South-East', Zone::find($created['id'])->name);

        $this->withHeaders($this->headers($admin))
            ->deleteJson("/api/v1/locations/zones/{$created['id']}")
            ->assertOk();

        $this->assertFalse(Zone::whereKey($created['id'])->exists());
    }

    public function test_a_zone_with_states_cannot_be_deleted(): void
    {
        $zone = Zone::create(['name' => 'NW', 'code' => 'NW']);
        State::create(['name' => 'Kano', 'zone_id' => $zone->id]);

        $this->withHeaders($this->headers($this->admin()))
            ->deleteJson("/api/v1/locations/zones/{$zone->id}")
            ->assertStatus(422)
            ->assertJsonPath('status', false);

        $this->assertTrue(Zone::whereKey($zone->id)->exists());
    }

    public function test_a_state_with_lgas_cannot_be_deleted(): void
    {
        $zone = Zone::create(['name' => 'NW', 'code' => 'NW']);
        $state = State::create(['name' => 'Kano', 'zone_id' => $zone->id]);
        Lga::create(['name' => 'Dala', 'state_id' => $state->id]);

        $this->withHeaders($this->headers($this->admin()))
            ->deleteJson("/api/v1/locations/states/{$state->id}")
            ->assertStatus(422);

        $this->assertTrue(State::whereKey($state->id)->exists());
    }

    public function test_an_lga_with_no_children_deletes_cleanly(): void
    {
        $zone = Zone::create(['name' => 'NW', 'code' => 'NW']);
        $state = State::create(['name' => 'Kano', 'zone_id' => $zone->id]);
        $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);

        $this->withHeaders($this->headers($this->admin()))
            ->deleteJson("/api/v1/locations/lgas/{$lga->id}")
            ->assertOk();

        $this->assertFalse(Lga::whereKey($lga->id)->exists());
    }

    public function test_creating_a_state_requires_a_real_zone(): void
    {
        $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/locations/states', ['name' => 'Nowhere', 'zone_id' => 999999])
            ->assertStatus(422);
    }

    public function test_creating_a_zone_requires_a_name(): void
    {
        $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/locations/zones', ['code' => 'XX'])
            ->assertStatus(422);
    }
}
