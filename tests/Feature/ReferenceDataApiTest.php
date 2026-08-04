<?php

namespace Tests\Feature;

use App\Models\DisagregationCategory;
use App\Models\SectoralGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReferenceDataApiTest extends TestCase
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

    // ==================== Auth boundary ====================

    public function test_reads_require_authentication(): void
    {
        $this->getJson('/api/v1/nlgas-pillars')->assertStatus(401);
    }

    public function test_writes_require_the_manage_settings_permission(): void
    {
        $this->withHeaders($this->headers($this->plainUser()))
            ->postJson('/api/v1/nlgas-pillars', ['code' => 'P1', 'title' => 'Pillar One', 'description' => 'Desc'])
            ->assertStatus(403);
    }

    // ==================== NL-GAS Pillars ====================

    public function test_admin_can_create_update_and_delete_a_pillar(): void
    {
        $admin = $this->admin();

        $created = $this->withHeaders($this->headers($admin))
            ->postJson('/api/v1/nlgas-pillars', ['code' => 'P1', 'title' => 'Pillar One', 'description' => 'Desc'])
            ->assertStatus(201)
            ->assertJsonPath('status', true)
            ->json('data');

        $this->assertSame('P1', $created['code']);
        $this->assertArrayNotHasKey('uuid', $created);

        $this->withHeaders($this->headers($admin))
            ->putJson("/api/v1/nlgas-pillars/{$created['id']}", ['code' => 'P1', 'title' => 'Pillar One Updated', 'description' => 'Desc'])
            ->assertOk();

        $this->assertSame('Pillar One Updated', \App\Models\NlgasPillar::find($created['id'])->title);

        $this->withHeaders($this->headers($admin))
            ->deleteJson("/api/v1/nlgas-pillars/{$created['id']}")
            ->assertOk();

        $this->assertFalse(\App\Models\NlgasPillar::whereKey($created['id'])->exists());
    }

    public function test_duplicate_pillar_code_is_rejected(): void
    {
        \App\Models\NlgasPillar::create(['code' => 'P1', 'title' => 'Pillar One', 'description' => 'Desc']);

        $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/nlgas-pillars', ['code' => 'P1', 'title' => 'Another', 'description' => 'Desc'])
            ->assertStatus(422);
    }

    public function test_creating_a_pillar_without_description_is_a_422_not_a_500(): void
    {
        $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/nlgas-pillars', ['code' => 'P1', 'title' => 'Pillar One'])
            ->assertStatus(422);
    }

    public function test_pillars_index_omits_uuid(): void
    {
        \App\Models\NlgasPillar::create(['code' => 'P1', 'title' => 'Pillar One', 'description' => 'Desc']);

        $data = $this->withHeaders($this->headers($this->admin()))
            ->getJson('/api/v1/nlgas-pillars')
            ->assertOk()
            ->json('data');

        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayNotHasKey('uuid', $data[0]);
    }

    // ==================== Sectoral Goals ====================

    public function test_admin_can_create_update_and_delete_a_sectoral_goal(): void
    {
        $admin = $this->admin();

        $created = $this->withHeaders($this->headers($admin))
            ->postJson('/api/v1/sectoral-goals', ['code' => 'G1', 'title' => 'Goal One', 'description' => 'Desc'])
            ->assertStatus(201)
            ->assertJsonPath('status', true)
            ->json('data');

        $this->assertSame('G1', $created['code']);
        $this->assertNotEmpty($created['uuid']);

        $this->withHeaders($this->headers($admin))
            ->putJson("/api/v1/sectoral-goals/{$created['id']}", ['code' => 'G1', 'title' => 'Goal One Updated', 'description' => 'Desc'])
            ->assertOk();

        $this->assertSame('Goal One Updated', SectoralGoal::find($created['id'])->title);

        $this->withHeaders($this->headers($admin))
            ->deleteJson("/api/v1/sectoral-goals/{$created['id']}")
            ->assertOk();

        $this->assertFalse(SectoralGoal::whereKey($created['id'])->exists());
    }

    public function test_duplicate_sectoral_goal_code_is_rejected(): void
    {
        SectoralGoal::create(['code' => 'G1', 'title' => 'Goal One', 'description' => 'Desc']);

        $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/sectoral-goals', ['code' => 'G1', 'title' => 'Another', 'description' => 'Desc'])
            ->assertStatus(422);
    }

    public function test_creating_a_sectoral_goal_without_description_is_a_422_not_a_500(): void
    {
        $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/sectoral-goals', ['code' => 'G1', 'title' => 'Goal One'])
            ->assertStatus(422);
    }

    public function test_new_sectoral_goals_endpoint_includes_id(): void
    {
        SectoralGoal::create(['code' => 'G1', 'title' => 'Goal One', 'description' => 'Desc']);

        $data = $this->withHeaders($this->headers($this->admin()))
            ->getJson('/api/v1/sectoral-goals')
            ->assertOk()
            ->json('data');

        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('uuid', $data[0]);
    }

    public function test_legacy_programs_sectoral_goals_endpoint_still_omits_id(): void
    {
        SectoralGoal::create(['code' => 'G1', 'title' => 'Goal One', 'description' => 'Desc']);

        $data = $this->getJson('/api/v1/programs/sectoral-goals')
            ->assertOk()
            ->json('data');

        $this->assertArrayNotHasKey('id', $data[0]);
        $this->assertArrayHasKey('uuid', $data[0]);
    }

    // ==================== Disaggregation Categories & Items ====================

    public function test_admin_can_create_a_category_with_nested_items(): void
    {
        $created = $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/disaggregation-categories', ['name' => 'Sex', 'items' => ['Male', 'Female']])
            ->assertStatus(201)
            ->assertJsonPath('status', true)
            ->json('data');

        $this->assertSame('Sex', $created['name']);
        $this->assertCount(2, $created['items']);
        $this->assertSame(['Male', 'Female'], array_column($created['items'], 'name'));
    }

    public function test_admin_can_add_update_and_delete_an_item_on_an_existing_category(): void
    {
        $admin = $this->admin();
        $category = DisagregationCategory::create(['name' => 'Sex']);

        $item = $this->withHeaders($this->headers($admin))
            ->postJson("/api/v1/disaggregation-categories/{$category->id}/items", ['name' => 'Male'])
            ->assertStatus(201)
            ->json('data');

        $this->assertSame('Male', $item['name']);

        $this->withHeaders($this->headers($admin))
            ->putJson("/api/v1/disaggregation-categories/{$category->id}/items/{$item['id']}", ['name' => 'M'])
            ->assertOk();

        $this->assertSame('M', $category->items()->find($item['id'])->name);

        $this->withHeaders($this->headers($admin))
            ->deleteJson("/api/v1/disaggregation-categories/{$category->id}/items/{$item['id']}")
            ->assertOk();

        $this->assertFalse($category->items()->whereKey($item['id'])->exists());
    }

    public function test_an_item_cannot_be_updated_or_deleted_through_a_different_categorys_url(): void
    {
        $admin = $this->admin();
        $ownerCategory = DisagregationCategory::create(['name' => 'Sex']);
        $otherCategory = DisagregationCategory::create(['name' => 'Age']);
        $item = $ownerCategory->items()->create(['name' => 'Male']);

        $this->withHeaders($this->headers($admin))
            ->putJson("/api/v1/disaggregation-categories/{$otherCategory->id}/items/{$item->id}", ['name' => 'Hijacked'])
            ->assertStatus(404);

        $this->withHeaders($this->headers($admin))
            ->deleteJson("/api/v1/disaggregation-categories/{$otherCategory->id}/items/{$item->id}")
            ->assertStatus(404);

        $this->assertSame('Male', $item->fresh()->name);
        $this->assertTrue(\App\Models\DisagregationItem::whereKey($item->id)->exists());
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        DisagregationCategory::create(['name' => 'Sex']);

        $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/disaggregation-categories', ['name' => 'Sex'])
            ->assertStatus(422);
    }

    public function test_deleting_a_category_cascade_deletes_its_items(): void
    {
        // History worth keeping: `disagregation_items.disagregation_category_id`
        // was originally declared via
        // `$table->unsignedBigInteger(...)->constrained()->onDelete('cascade')`
        // in 2026_02_23_110450_create_disagregation_items_table.php. `constrained()`
        // is only meaningful on the ForeignIdColumnDefinition that `foreignId()`
        // returns; on a plain unsignedBigInteger it is absorbed by Fluent's magic
        // __call and silently registers nothing — so for a long time there was no
        // foreign key at all, and deleting a category left its items orphaned.
        // 2026_08_03_000000_add_foreign_key_to_disagregation_items_table adds the
        // constraint for real (after clearing pre-existing orphans), so the cascade
        // now actually happens. This test asserts that.
        //
        // Note this only holds on a driver that supports ALTER TABLE ... ADD
        // CONSTRAINT — the migration deliberately skips SQLite. The suite runs on
        // PostgreSQL (see phpunit.xml), which is also what production uses.
        $category = DisagregationCategory::create(['name' => 'Sex']);
        $item = $category->items()->create(['name' => 'Male']);

        $this->withHeaders($this->headers($this->admin()))
            ->deleteJson("/api/v1/disaggregation-categories/{$category->id}")
            ->assertOk();

        $this->assertFalse(DisagregationCategory::whereKey($category->id)->exists());
        $this->assertFalse(\App\Models\DisagregationItem::whereKey($item->id)->exists());
    }
}
