<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Services\SettingService;
use Database\Seeders\ApprovalWorkflowSeeder;
use Database\Seeders\IndicatorReportingSettingsSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_permissions_roles_workflow_and_setting(): void
    {
        Department::create(['name' => 'Livestock', 'slug' => 'livestock']);

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(ApprovalWorkflowSeeder::class);
        $this->seed(IndicatorReportingSettingsSeeder::class);

        $this->assertNotNull(Permission::where('permission', 'review-indicator-reports')->first());
        $this->assertNotNull(Role::where('slug', 'prs')->first());

        $wf = ApprovalWorkflow::where('slug', 'standard-me-approval')->first();
        $this->assertNotNull($wf);
        $this->assertSame(['PRS Validation', 'Permanent Secretary Approval'], $wf->stages->pluck('name')->all());
        $this->assertTrue($wf->departments->contains(fn ($d) => $d->slug === 'livestock'));

        $this->assertFalse(app(SettingService::class)->get(SettingService::ALLOW_SUPPORTING_DEPT));
    }
}
