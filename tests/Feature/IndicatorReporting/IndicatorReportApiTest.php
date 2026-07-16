<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\OutputIndicator;
use App\Models\Permission;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

class IndicatorReportApiTest extends TestCase
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

    public function test_director_creates_and_submits_report(): void
    {
        Storage::fake();
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $prs = Role::create(['name' => 'PRS', 'slug' => 'prs']);
        $wf = ApprovalWorkflow::create(['name' => 'WF', 'slug' => 'wf', 'is_active' => true, 'resubmit_behavior' => 'from_start']);
        $wf->stages()->create(['name' => 'Validate', 'position' => 1, 'assignment_type' => 'role', 'role_id' => $prs->id, 'approval_mode' => 'any']);
        $wf->departments()->attach($dept->id);

        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = $this->directorFor($dept);

        $create = $this->withHeaders($this->authHeaders($director))->postJson('/api/v1/indicator-reports', [
            'indicator_type' => OutputIndicator::class,
            'indicator_id' => $indicator->id,
            'department_id' => $dept->id,
            'reporting_period_id' => $period->id,
            'target_value' => 100, 'actual_value' => 80, 'narrative' => 'done',
            'values' => [['disagregation_item_id' => null, 'value' => 80]],
        ])->assertCreated();

        $uuid = $create->json('data.uuid');

        $this->withHeaders($this->authHeaders($director))
            ->postJson("/api/v1/indicator-reports/{$uuid}/proofs", ['file' => UploadedFile::fake()->create('proof.pdf', 20, 'application/pdf')])
            ->assertCreated();

        $this->withHeaders($this->authHeaders($director))
            ->postJson("/api/v1/indicator-reports/{$uuid}/submit")
            ->assertOk()->assertJsonPath('data.status', 'pending');
    }

    public function test_director_cannot_report_indicator_of_other_department(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $other = Department::create(['name' => 'Finance', 'slug' => 'finance']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $other->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = $this->directorFor($dept);

        $this->withHeaders($this->authHeaders($director))->postJson('/api/v1/indicator-reports', [
            'indicator_type' => OutputIndicator::class, 'indicator_id' => $indicator->id,
            'department_id' => $other->id, 'reporting_period_id' => $period->id,
            'target_value' => 1, 'actual_value' => 1,
        ])->assertForbidden();
    }
}
