<?php

namespace Tests\Feature;

use App\Http\Controllers\DepartmentController;
use App\Models\Department;
use App\Models\OutputIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentIndicatorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_lists_result_chain_indicators_it_owns(): void
    {
        $dept = Department::create(['name' => 'Vet Services', 'slug' => 'vet-services']);
        $oi = OutputIndicator::create(['title' => 'Output A', 'department_id' => $dept->id]);

        $indicators = app(DepartmentController::class)->resultChainIndicatorsFor($dept);

        $this->assertTrue(collect($indicators)->pluck('code')->contains($oi->code));
    }
}
