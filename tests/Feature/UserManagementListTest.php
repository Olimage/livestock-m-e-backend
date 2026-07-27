<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The Users Management list is a session-authenticated Inertia page. Its sort
 * parameters come straight off the query string, and `users.role` no longer
 * exists as a column — both are easy ways to 500 the screen.
 */
class UserManagementListTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'full_name' => 'Admin', 'email' => 'admin@x.io', 'password' => 'secret123', 'is_admin' => true,
        ]);
    }

    public function test_the_list_renders_with_roles_and_departments_loaded(): void
    {
        $dept = Department::create(['name' => 'Animal Health', 'slug' => 'animal-health']);
        $role = Role::create(['name' => 'Director', 'slug' => 'director']);
        $user = User::create(['full_name' => 'Dir', 'email' => 'dir@x.io', 'password' => 'secret123']);
        $user->roles()->attach($role->id);
        $user->departments()->attach($dept->id);

        $this->actingAs($this->admin())
            ->get(route('users.index'))
            ->assertOk()
            // The Role column reads user.roles[].name — without the eager load
            // it renders blank for every row.
            ->assertInertia(fn ($page) => $page
                ->has('users.data')
                ->where('users.data.0.roles.0.name', fn ($name) => $name !== null)
            );
    }

    /**
     * `role` and `department` are relations, not columns on `users`. Passing
     * either to orderBy() raises SQLSTATE[42703] on PostgreSQL.
     *
     * The suite runs on SQLite, which does NOT error here — an unresolvable
     * double-quoted identifier in ORDER BY is treated as a string literal, so it
     * silently sorts by a constant. assertOk() therefore proves nothing; assert
     * on the generated ORDER BY clause instead.
     */
    public function test_an_unknown_sort_column_never_reaches_the_query(): void
    {
        $admin = $this->admin();

        foreach (['role', 'department', 'password', 'not_a_column', '"; drop table users; --'] as $sortBy) {
            DB::enableQueryLog();
            $this->actingAs($admin)
                ->get(route('users.index', ['sort_by' => $sortBy, 'sort_order' => 'asc']))
                ->assertOk();
            $sql = collect(DB::getQueryLog())->pluck('query')->implode(' ');
            DB::disableQueryLog();

            preg_match_all('/order by (.+?)(?= limit|$)/i', $sql, $matches);
            $orderBy = implode(' ', $matches[1]);

            $this->assertNotEmpty($orderBy, 'expected an ordered query');
            $this->assertStringNotContainsString($sortBy, $orderBy, "'{$sortBy}' reached the ORDER BY clause");
            $this->assertStringContainsString('created_at', $orderBy, 'should fall back to the default column');
        }

        $this->assertTrue(User::query()->exists(), 'users table should be intact');
    }

    public function test_an_invalid_sort_direction_falls_back_rather_than_erroring(): void
    {
        DB::enableQueryLog();
        $this->actingAs($this->admin())
            ->get(route('users.index', ['sort_by' => 'full_name', 'sort_order' => 'sideways']))
            ->assertOk();
        $sql = collect(DB::getQueryLog())->pluck('query')->implode(' ');
        DB::disableQueryLog();

        $this->assertStringNotContainsString('sideways', $sql);
    }

    public function test_sorting_by_an_allowed_column_works(): void
    {
        User::create(['full_name' => 'Zoe', 'email' => 'z@x.io', 'password' => 'secret123']);
        User::create(['full_name' => 'Aaron', 'email' => 'a@x.io', 'password' => 'secret123']);

        $this->actingAs($this->admin())
            ->get(route('users.index', ['sort_by' => 'full_name', 'sort_order' => 'asc']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('users.data.0.full_name', 'Aaron'));
    }

    /**
     * The search clause must be grouped. Ungrouped, `orWhere` escapes the
     * whereDoesntHave guard through AND/OR precedence and super_admins leak
     * into a list that is meant to exclude them.
     */
    public function test_search_cannot_surface_a_super_admin(): void
    {
        $superRole = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin']);
        $super = User::create(['full_name' => 'Hidden Boss', 'email' => 'boss@x.io', 'password' => 'secret123']);
        $super->roles()->attach($superRole->id);

        $this->actingAs($this->admin())
            ->get(route('users.index', ['search' => 'Hidden Boss']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where(
                'users.data',
                fn ($rows) => collect($rows)->pluck('email')->doesntContain('boss@x.io')
            ));
    }
}
