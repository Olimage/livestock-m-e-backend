<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SupervisorEnumerator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Exercises the supervisor/enumerator API (routes/v1/supervisor-enumerator.php).
 *
 * Roles are attached via the user_role pivot (the users.role string column was
 * dropped in 2026_07_16_000004_move_user_role_string_to_pivot). The assign/remove
 * endpoints return redirect-back responses with a flash message (the controller is
 * shared with the Inertia web UI); getEnumerators returns JSON.
 */
class SupervisorEnumeratorTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(?string $slug): User
    {
        $user = User::factory()->create();

        if ($slug !== null) {
            $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);
            $user->roles()->attach($role->id);
        }

        return $user;
    }

    /** Headers that authenticate the request against the jwt-backed `api` guard. */
    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.JWTAuth::fromUser($user)];
    }

    private function actor(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_can_assign_enumerator_to_supervisor(): void
    {
        $supervisor = $this->userWithRole('supervisor');
        $enumerator = $this->userWithRole('enumerator');

        $this->withHeaders($this->authHeaders($this->actor()))
            ->postJson('/api/v1/supervisor-enumerators/assign', [
                'supervisor_id' => $supervisor->id,
                'enumerator_ids' => [$enumerator->id],
            ])
            ->assertStatus(302)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('supervisor_enumerators', [
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $enumerator->id,
        ]);
    }

    public function test_cannot_assign_non_supervisor(): void
    {
        $nonSupervisor = $this->userWithRole(null);
        $enumerator = $this->userWithRole('enumerator');

        $this->withHeaders($this->authHeaders($this->actor()))
            ->postJson('/api/v1/supervisor-enumerators/assign', [
                'supervisor_id' => $nonSupervisor->id,
                'enumerator_ids' => [$enumerator->id],
            ])
            ->assertStatus(302)
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('supervisor_enumerators', [
            'supervisor_id' => $nonSupervisor->id,
            'enumerator_id' => $enumerator->id,
        ]);
    }

    public function test_cannot_assign_non_enumerator(): void
    {
        $supervisor = $this->userWithRole('supervisor');
        $nonEnumerator = $this->userWithRole(null);

        $this->withHeaders($this->authHeaders($this->actor()))
            ->postJson('/api/v1/supervisor-enumerators/assign', [
                'supervisor_id' => $supervisor->id,
                'enumerator_ids' => [$nonEnumerator->id],
            ])
            ->assertStatus(302)
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('supervisor_enumerators', [
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $nonEnumerator->id,
        ]);
    }

    public function test_can_remove_assignment(): void
    {
        $supervisor = $this->userWithRole('supervisor');
        $enumerator = $this->userWithRole('enumerator');

        SupervisorEnumerator::create([
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $enumerator->id,
        ]);

        $this->withHeaders($this->authHeaders($this->actor()))
            ->deleteJson("/api/v1/supervisor-enumerators/{$supervisor->id}/{$enumerator->id}")
            ->assertStatus(302)
            ->assertSessionHas('success', 'Enumerator unassigned successfully');

        $this->assertDatabaseMissing('supervisor_enumerators', [
            'supervisor_id' => $supervisor->id,
            'enumerator_id' => $enumerator->id,
        ]);
    }

    public function test_can_get_supervisor_enumerators(): void
    {
        $supervisor = $this->userWithRole('supervisor');
        $enumerator1 = $this->userWithRole('enumerator');
        $enumerator2 = $this->userWithRole('enumerator');

        SupervisorEnumerator::create(['supervisor_id' => $supervisor->id, 'enumerator_id' => $enumerator1->id]);
        SupervisorEnumerator::create(['supervisor_id' => $supervisor->id, 'enumerator_id' => $enumerator2->id]);

        $this->withHeaders($this->authHeaders($this->actor()))
            ->getJson("/api/v1/supervisor-enumerators/{$supervisor->id}/enumerators")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'email', 'assigned_at'],
            ]);
    }
}
