<?php

namespace Tests\Feature;

use App\Models\SupervisorEnumerator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorEnumeratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_assign_enumerator_to_supervisor()
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $enumerator = User::factory()->create(['role' => 'enumerator']);

        $response = $this->postJson('/api/v1/supervisor-enumerators/assign', [
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $enumerator->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'relationship' => [
                    'supervisor',
                    'enumerator',
                ],
            ]);

        $this->assertDatabaseHas('supervisor_enumerators', [
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $enumerator->id,
        ]);
    }

    public function test_cannot_assign_non_supervisor()
    {
        $nonSupervisor = User::factory()->create(['role' => null]);
        $enumerator = User::factory()->create(['role' => 'enumerator']);

        $response = $this->postJson('/api/v1/supervisor-enumerators/assign', [
            'supervisor_id' => $nonSupervisor->id,
            'enumerator_id' => $enumerator->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supervisor_id']);
    }

    public function test_cannot_assign_non_enumerator()
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $nonEnumerator = User::factory()->create(['role' => null]);

        $response = $this->postJson('/api/v1/supervisor-enumerators/assign', [
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $nonEnumerator->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['enumerator_id']);
    }

    public function test_can_remove_assignment()
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $enumerator = User::factory()->create(['role' => 'enumerator']);

        $relationship = SupervisorEnumerator::create([
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $enumerator->id,
        ]);

        $response = $this->deleteJson("/api/v1/supervisor-enumerators/{$supervisor->id}/{$enumerator->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Enumerator unassigned successfully']);

        $this->assertDatabaseMissing('supervisor_enumerators', [
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $enumerator->id,
        ]);
    }

    public function test_can_get_supervisor_enumerators()
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $enumerator1 = User::factory()->create(['role' => 'enumerator']);
        $enumerator2 = User::factory()->create(['role' => 'enumerator']);

        SupervisorEnumerator::create([
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $enumerator1->id,
        ]);
        SupervisorEnumerator::create([
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $enumerator2->id,
        ]);

        $response = $this->getJson("/api/v1/supervisor-enumerators/{$supervisor->id}/enumerators");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'email',
                    'assigned_at',
                ],
            ]);
    }
}