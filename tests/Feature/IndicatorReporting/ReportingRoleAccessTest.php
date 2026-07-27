<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\Department;
use App\Models\OutputIndicator;
use App\Models\Permission;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

/**
 * Documents which permission each reporting endpoint requires.
 *
 * These are deliberately expressed as test fixtures, NOT as a seeder: who holds
 * which permission is runtime configuration owned by the permission admin
 * screens, and approver eligibility is owned by the approval workflow module
 * (ApprovalWorkflowStage decides per stage, by role or by named users). Nothing
 * here should encode ministry access policy.
 *
 * The point of these tests is the contract — "this endpoint needs this
 * permission" — so a rename or an accidental tightening is caught.
 */
class ReportingRoleAccessTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    private Department $dept;

    private OutputIndicator $indicator;

    private ReportingPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::create(['name' => 'Animal Health', 'slug' => 'animal-health']);
        $this->indicator = OutputIndicator::create(['title' => 'Outbreaks', 'department_id' => $this->dept->id]);
        $this->period = ReportingPeriod::create([
            'name' => 'Q4 2027', 'type' => 'quarter', 'year' => 2027, 'period_number' => 4, 'is_open' => true,
        ]);
    }

    /**
     * A user carrying exactly the given permissions — mirrors an admin granting
     * them through the permission screens.
     *
     * @param  array<int, string>  $permissions
     */
    private function userWith(array $permissions, bool $inDepartment = true): User
    {
        $role = Role::create(['name' => 'R'.uniqid(), 'slug' => 'r'.uniqid()]);

        foreach ($permissions as $key) {
            $permission = Permission::firstOrCreate(
                ['permission' => $key],
                ['label' => ucwords(str_replace('-', ' ', $key))]
            );
            $role->permissions()->attach($permission->id);
        }

        $user = User::create([
            'full_name' => 'Tester', 'email' => uniqid().'@test.local', 'password' => 'secret123',
        ]);
        $user->roles()->attach($role->id);

        if ($inDepartment) {
            $user->departments()->attach($this->dept->id);
        }

        return $user;
    }

    private function createPayload(): array
    {
        return [
            'indicator_type' => OutputIndicator::class,
            'indicator_id' => $this->indicator->id,
            'department_id' => $this->dept->id,
            'reporting_period_id' => $this->period->id,
            'baseline' => 2000,
            'baseline_year' => 2027,
            'target_value' => 4567,
            'actual_value' => 5679,
        ];
    }

    public function test_creating_a_report_requires_report_indicator_data(): void
    {
        $granted = $this->userWith(['report-indicator-data']);

        $this->withHeaders($this->authHeaders($granted))
            ->postJson('/api/v1/indicator-reports', $this->createPayload())
            ->assertCreated()
            ->assertJsonPath('data.baseline_year', 2027);
    }

    public function test_a_user_without_that_permission_is_refused(): void
    {
        // e.g. holding only the dashboard-side `create-report`, which the UI uses
        // for button visibility but this endpoint does not check.
        $ungranted = $this->userWith(['create-report']);

        $this->withHeaders($this->authHeaders($ungranted))
            ->postJson('/api/v1/indicator-reports', $this->createPayload())
            ->assertForbidden();
    }

    public function test_the_permission_does_not_override_department_ownership(): void
    {
        $outsider = $this->userWith(['report-indicator-data'], inDepartment: false);

        $this->withHeaders($this->authHeaders($outsider))
            ->postJson('/api/v1/indicator-reports', $this->createPayload())
            ->assertForbidden();
    }

    public function test_approving_requires_a_review_or_approve_permission(): void
    {
        $reporter = $this->userWith(['report-indicator-data']);
        $created = $this->withHeaders($this->authHeaders($reporter))
            ->postJson('/api/v1/indicator-reports', $this->createPayload())
            ->assertCreated();
        $uuid = $created->json('data.uuid');

        $this->flushHeaders();
        $noRights = $this->userWith(['create-report']);
        $this->withHeaders($this->authHeaders($noRights))
            ->postJson("/api/v1/indicator-reports/{$uuid}/approve")
            ->assertForbidden();
    }

    public function test_seeing_other_peoples_reports_requires_view_all(): void
    {
        $reporter = $this->userWith(['report-indicator-data']);
        $this->withHeaders($this->authHeaders($reporter))
            ->postJson('/api/v1/indicator-reports', $this->createPayload())
            ->assertCreated();

        // Without view-all the list silently narrows to the caller's own reports
        // — an empty queue rather than a 403, which is easy to misread as "no
        // reports submitted".
        $this->flushHeaders();
        $limited = $this->userWith(['review-indicator-reports']);
        $this->assertEmpty(
            $this->withHeaders($this->authHeaders($limited))
                ->getJson('/api/v1/indicator-reports')->assertOk()->json('data.data')
        );

        $this->flushHeaders();
        $full = $this->userWith(['review-indicator-reports', 'view-all-indicator-reports']);
        $this->assertNotEmpty(
            $this->withHeaders($this->authHeaders($full))
                ->getJson('/api/v1/indicator-reports')->assertOk()->json('data.data')
        );
    }
}
