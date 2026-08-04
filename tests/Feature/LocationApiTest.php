<?php

namespace Tests\Feature;

use App\Models\Lga;
use App\Models\State;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LocationApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'full_name' => 'Admin',
            'email' => uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'is_admin' => true,
        ]);
    }

    private function authHeaders(?User $user = null): array
    {
        $token = JWTAuth::fromUser($user ?? $this->user());

        return ['Authorization' => "Bearer {$token}"];
    }

    private function seedGeography(): array
    {
        $nw = Zone::create(['name' => 'North West', 'code' => 'NW']);
        $nc = Zone::create(['name' => 'North Central', 'code' => 'NC']);
        $kano = State::create(['name' => 'Kano', 'zone_id' => $nw->id]);
        $benue = State::create(['name' => 'Benue', 'zone_id' => $nc->id]);
        Lga::create(['name' => 'Nassarawa', 'state_id' => $kano->id]);
        Lga::create(['name' => 'Dala', 'state_id' => $kano->id]);
        Lga::create(['name' => 'Makurdi', 'state_id' => $benue->id]);

        return compact('nw', 'nc', 'kano', 'benue');
    }

    public function test_locations_require_authentication(): void
    {
        $this->getJson('/api/v1/locations/zones')->assertStatus(401);
        $this->getJson('/api/v1/locations/states')->assertStatus(401);
        $this->getJson('/api/v1/locations/lgas')->assertStatus(401);
    }

    public function test_zones_include_a_state_count(): void
    {
        $this->seedGeography();

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/zones')
            ->assertOk()
            ->assertJsonPath('status', true);

        $zones = collect($response->json('data'));
        $this->assertSame(2, $zones->count());

        $nw = $zones->firstWhere('code', 'NW');
        $this->assertSame('North West', $nw['name']);
        $this->assertSame(1, $nw['states_count']);
        $this->assertArrayHasKey('uuid', $nw);
    }

    // The whole point of this task: the old endpoint required zone_id.
    public function test_states_list_everything_when_no_zone_id_is_given(): void
    {
        $this->seedGeography();

        $states = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/states')
            ->assertOk()
            ->json('data');

        $this->assertCount(2, $states);
    }

    public function test_states_carry_their_zone_code_and_lga_count(): void
    {
        $this->seedGeography();

        $states = collect($this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/states')->json('data'));

        $kano = $states->firstWhere('name', 'Kano');
        $this->assertSame('NW', $kano['zone_code']);
        $this->assertSame('North West', $kano['zone_name']);
        $this->assertSame(2, $kano['lgas_count']);
    }

    public function test_states_still_filter_by_zone_id_when_it_is_supplied(): void
    {
        $seed = $this->seedGeography();

        $states = $this->withHeaders($this->authHeaders())
            ->getJson("/api/v1/locations/states?zone_id={$seed['nw']->id}")
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $states);
        $this->assertSame('Kano', $states[0]['name']);
    }

    public function test_lgas_list_everything_and_carry_their_parents(): void
    {
        $this->seedGeography();

        $lgas = collect($this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/lgas')->json('data'));

        $this->assertSame(3, $lgas->count());

        $nassarawa = $lgas->firstWhere('name', 'Nassarawa');
        $this->assertSame('Kano', $nassarawa['state_name']);
        $this->assertSame('NW', $nassarawa['zone_code']);
    }

    public function test_lgas_filter_by_state_and_by_zone(): void
    {
        $seed = $this->seedGeography();

        $byState = $this->withHeaders($this->authHeaders())
            ->getJson("/api/v1/locations/lgas?state_id={$seed['kano']->id}")->json('data');
        $this->assertCount(2, $byState);

        $byZone = $this->withHeaders($this->authHeaders())
            ->getJson("/api/v1/locations/lgas?zone_id={$seed['nc']->id}")->json('data');
        $this->assertCount(1, $byZone);
        $this->assertSame('Makurdi', $byZone[0]['name']);
    }

    public function test_search_matches_on_name(): void
    {
        $this->seedGeography();

        $lgas = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/lgas?search=maku')->json('data');

        $this->assertCount(1, $lgas);
        $this->assertSame('Makurdi', $lgas[0]['name']);
    }

    public function test_per_page_switches_to_a_paginator(): void
    {
        $this->seedGeography();

        $body = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/lgas?per_page=2')->json('data');

        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('total', $body);
        $this->assertCount(2, $body['data']);
        $this->assertSame(3, $body['total']);
    }

    public function test_an_unknown_zone_id_is_rejected_rather_than_ignored(): void
    {
        $this->seedGeography();

        $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/states?zone_id=999999')
            ->assertStatus(422);
    }
}
