<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\BondOutputIndicator;
use App\Models\Department;
use App\Models\IndicatorReport;
use App\Models\OutputIndicator;
use App\Models\Permission;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

/**
 * Covers the payload/response shape mne_frontend's indicator reporting form
 * depends on: department-scoped indicator options, department ids, baseline
 * fields, and addressing a report by either uuid or id.
 */
class ReportingFormContractTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    private function directorFor(Department $dept): User
    {
        $perm = Permission::create(['permission' => 'report-indicator-data', 'label' => 'Report Indicator Data']);
        $role = Role::create(['name' => 'Director', 'slug' => 'director']);
        $role->permissions()->attach($perm->id);
        $user = User::create(['full_name' => 'Dir', 'email' => 'dir@x.io', 'password' => 'secret123']);
        $user->roles()->attach($role->id);
        $user->departments()->attach($dept->id);

        return $user;
    }

    public function test_program_indicators_expose_owning_department_id(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $owned = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id]);
        $bond = BondOutputIndicator::create(['title' => 'Bond A']);

        $rows = collect($this->getJson('/api/v1/programs/indicators')->assertOk()->json('data'));

        $this->assertSame(
            $dept->id,
            $rows->firstWhere('code', $owned->code)['department_id']
        );

        // Bond Output carries no department column — the key is still present, null.
        $bondRow = $rows->firstWhere('code', $bond->code);
        $this->assertArrayHasKey('department_id', $bondRow);
        $this->assertNull($bondRow['department_id']);
    }

    public function test_departments_endpoint_exposes_ids(): void
    {
        $parent = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $child = Department::create(['name' => 'Vet Services', 'slug' => 'vet-services', 'parent_id' => $parent->id]);

        $data = $this->getJson('/api/v1/departments')->assertOk()->json('data');

        $this->assertSame($parent->id, $data[0]['id']);
        $this->assertSame($child->id, collect($data[0]['descendants'])->firstWhere('slug', 'vet-services')['id']);
    }

    public function test_me_returns_the_users_departments(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $director = $this->directorFor($dept);

        $this->withHeaders($this->authHeaders($director))
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.departments.0.id', $dept->id)
            ->assertJsonPath('data.user.departments.0.slug', 'livestock');
    }

    public function test_report_persists_and_returns_baseline_fields(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id]);
        $period = ReportingPeriod::create(['name' => 'Q1 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = $this->directorFor($dept);

        $create = $this->withHeaders($this->authHeaders($director))
            ->postJson('/api/v1/indicator-reports', [
                'indicator_type' => OutputIndicator::class,
                'indicator_id' => $indicator->id,
                'department_id' => $dept->id,
                'reporting_period_id' => $period->id,
                'baseline' => 3800,
                'baseline_year' => 2024,
                'target_value' => 5000,
                'actual_value' => 4200,
            ])->assertCreated();

        $this->assertSame('3800.0000', $create->json('data.baseline'));
        $this->assertSame(2024, $create->json('data.baseline_year'));

        $this->withHeaders($this->authHeaders($director))
            ->putJson('/api/v1/indicator-reports/'.$create->json('data.uuid'), [
                'baseline' => 3900,
                'baseline_year' => 2025,
            ])
            ->assertOk()
            ->assertJsonPath('data.baseline_year', 2025);
    }

    public function test_baseline_year_is_bounded(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id]);
        $period = ReportingPeriod::create(['name' => 'Q1 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = $this->directorFor($dept);

        $this->withHeaders($this->authHeaders($director))
            ->postJson('/api/v1/indicator-reports', [
                'indicator_type' => OutputIndicator::class,
                'indicator_id' => $indicator->id,
                'department_id' => $dept->id,
                'reporting_period_id' => $period->id,
                'baseline_year' => 1999,
            ])
            ->assertJsonValidationErrors('baseline_year');
    }

    public function test_a_report_can_be_addressed_by_id_or_uuid(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id]);
        $period = ReportingPeriod::create(['name' => 'Q1 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = $this->directorFor($dept);

        $create = $this->withHeaders($this->authHeaders($director))
            ->postJson('/api/v1/indicator-reports', [
                'indicator_type' => OutputIndicator::class,
                'indicator_id' => $indicator->id,
                'department_id' => $dept->id,
                'reporting_period_id' => $period->id,
            ])->assertCreated();

        $id = $create->json('data.id');
        $uuid = $create->json('data.uuid');

        // mne_frontend addresses reports by `id`.
        $this->withHeaders($this->authHeaders($director))
            ->getJson("/api/v1/indicator-reports/{$id}")
            ->assertOk()
            ->assertJsonPath('data.uuid', $uuid);

        $this->withHeaders($this->authHeaders($director))
            ->getJson("/api/v1/indicator-reports/{$uuid}")
            ->assertOk()
            ->assertJsonPath('data.id', $id);

        $this->withHeaders($this->authHeaders($director))
            ->getJson('/api/v1/indicator-reports/does-not-exist')
            ->assertNotFound();
    }

    /**
     * The suite runs on SQLite but production is PostgreSQL, which refuses to
     * compare a `uuid` column against a non-uuid literal (SQLSTATE[22P02])
     * instead of simply not matching. SQLite coerces silently, so assert on the
     * generated SQL rather than on the result.
     */
    public function test_a_numeric_key_is_never_compared_against_the_uuid_column(): void
    {
        DB::enableQueryLog();
        (new IndicatorReport)->resolveRouteBinding('227');
        $sql = collect(DB::getQueryLog())->pluck('query')->implode(' ');
        DB::disableQueryLog();

        $this->assertNotEmpty($sql, 'expected a lookup query');
        $this->assertStringNotContainsString('uuid', $sql);
    }

    public function test_a_malformed_key_is_never_compared_against_the_uuid_column(): void
    {
        DB::enableQueryLog();
        $resolved = (new IndicatorReport)->resolveRouteBinding('does-not-exist');
        $sql = collect(DB::getQueryLog())->pluck('query')->implode(' ');
        DB::disableQueryLog();

        $this->assertNull($resolved);
        $this->assertStringNotContainsString('uuid', $sql);
    }

    public function test_a_uuid_key_is_never_compared_against_the_id_column(): void
    {
        DB::enableQueryLog();
        (new IndicatorReport)->resolveRouteBinding((string) Str::uuid());
        $sql = collect(DB::getQueryLog())->pluck('query')->implode(' ');
        DB::disableQueryLog();

        $this->assertStringContainsString('uuid', $sql);
        $this->assertStringNotContainsString('"id" =', $sql);
    }
}
