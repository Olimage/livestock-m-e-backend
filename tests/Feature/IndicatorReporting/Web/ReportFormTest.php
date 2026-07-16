<?php

namespace Tests\Feature\IndicatorReporting\Web;

use App\Enums\ReportStatus;
use App\Models\Department;
use App\Models\IndicatorReport;
use App\Models\OutputIndicator;
use App\Models\Permission;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFormTest extends TestCase
{
    use RefreshDatabase;

    private function director(Department $dept): User
    {
        $perm = Permission::create(['permission' => 'report-indicator-data', 'label' => 'Report']);
        $role = Role::create(['name' => 'Director', 'slug' => 'director']);
        $role->permissions()->attach($perm->id);
        $u = User::create(['full_name' => 'Dir', 'email' => 'dir@x.io', 'password' => 'secret123']);
        $u->roles()->attach($role->id);
        $u->departments()->attach($dept->id);

        return $u;
    }

    public function test_director_stores_then_submits_report(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $dept->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = $this->director($dept);

        $this->actingAs($director)->post(route('indicator-reporting.reports.store'), [
            'indicator_type' => OutputIndicator::class,
            'indicator_id' => $indicator->id,
            'department_id' => $dept->id,
            'reporting_period_id' => $period->id,
            'target_value' => 100, 'actual_value' => 80, 'narrative' => 'done',
        ])->assertRedirect();

        $report = IndicatorReport::first();
        $this->assertNotNull($report);
        $this->assertSame($director->id, $report->created_by);
        $this->assertSame(ReportStatus::Draft, $report->status);
    }

    public function test_other_department_store_is_blocked_with_flash_error(): void
    {
        $dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $other = Department::create(['name' => 'Finance', 'slug' => 'finance']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $other->id, 'measurement_unit' => 'count']);
        $period = ReportingPeriod::create(['name' => 'Q1', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);
        $director = $this->director($dept);

        $this->actingAs($director)->post(route('indicator-reporting.reports.store'), [
            'indicator_type' => OutputIndicator::class,
            'indicator_id' => $indicator->id,
            'department_id' => $other->id,
            'reporting_period_id' => $period->id,
            'actual_value' => 1,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame(0, IndicatorReport::count());
    }
}
