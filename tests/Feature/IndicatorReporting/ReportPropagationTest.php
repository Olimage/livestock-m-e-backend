<?php

namespace Tests\Feature\IndicatorReporting;

use App\Enums\ReportStatus;
use App\Models\Department;
use App\Models\IndicatorReport;
use App\Models\OutputIndicator;
use App\Models\ReportingPeriod;
use App\Support\IndicatorPerformance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A report only counts as ministry data once it has been approved through the
 * workflow. IndicatorPerformance is the shared shaper behind the dashboard,
 * bond-deliverable, sector-outcome and result-chain endpoints, so the approval
 * gate has to live there or unreviewed numbers reach every dashboard at once.
 */
class ReportPropagationTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    private OutputIndicator $indicator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $this->indicator = OutputIndicator::create([
            'title' => 'Vaccinations', 'department_id' => $this->dept->id, 'measurement_unit' => 'count',
        ]);
    }

    private function period(int $year, int $number): ReportingPeriod
    {
        return ReportingPeriod::create([
            'name' => "Q{$number} {$year}", 'type' => 'quarter', 'year' => $year, 'period_number' => $number,
        ]);
    }

    private function report(ReportingPeriod $period, ReportStatus $status, array $attrs = []): IndicatorReport
    {
        return IndicatorReport::create(array_merge([
            'indicator_type' => OutputIndicator::class,
            'indicator_id' => $this->indicator->id,
            'department_id' => $this->dept->id,
            'reporting_period_id' => $period->id,
            'status' => $status,
            'created_by' => \App\Models\User::create([
                'full_name' => 'R'.uniqid(), 'email' => uniqid().'@x.io', 'password' => 'secret123',
            ])->id,
        ], $attrs));
    }

    public function test_unapproved_reports_do_not_reach_the_dashboards(): void
    {
        $period = $this->period(2026, 1);

        foreach ([ReportStatus::Draft, ReportStatus::Pending, ReportStatus::Returned] as $status) {
            IndicatorReport::query()->forceDelete();
            $this->report($period, $status, ['target_value' => 100, 'actual_value' => 90]);

            $block = IndicatorPerformance::present($this->indicator, OutputIndicator::class);

            $this->assertNull($block['actual'], "{$status->value} report leaked into the dashboard");
            $this->assertNull($block['target'], "{$status->value} report leaked into the dashboard");
            $this->assertSame('unknown', $block['status']);
        }
    }

    public function test_approved_reports_do_reach_the_dashboards(): void
    {
        $this->report($this->period(2026, 1), ReportStatus::Approved, [
            'target_value' => 100, 'actual_value' => 95, 'published_at' => now(),
        ]);

        $block = IndicatorPerformance::present($this->indicator, OutputIndicator::class);

        $this->assertSame(95.0, $block['actual']);
        $this->assertSame(100.0, $block['target']);
        $this->assertSame('on', $block['status']);
        $this->assertSame('Livestock', $block['department']);
    }

    public function test_baseline_entered_on_the_form_reaches_the_dashboards(): void
    {
        $this->report($this->period(2026, 1), ReportStatus::Approved, [
            'baseline' => 3800, 'baseline_year' => 2024, 'target_value' => 5000, 'actual_value' => 4200,
        ]);

        $block = IndicatorPerformance::present($this->indicator, OutputIndicator::class);

        $this->assertSame(3800.0, $block['baseline']);
        $this->assertSame(2024, $block['baselineYear']);
    }

    public function test_latest_report_follows_period_chronology_not_insertion_order(): void
    {
        // Created newest-first so the later period has the LOWER id — ordering by
        // reporting_period_id would pick the wrong (older) report.
        $q4 = $this->period(2026, 4);
        $q1 = $this->period(2026, 1);

        $this->report($q4, ReportStatus::Approved, ['target_value' => 100, 'actual_value' => 80]);
        $this->report($q1, ReportStatus::Approved, ['target_value' => 100, 'actual_value' => 20]);

        $block = IndicatorPerformance::present($this->indicator, OutputIndicator::class);

        $this->assertSame(80.0, $block['actual'], 'Should surface Q4 2026, the chronologically latest period');
    }

    public function test_evidence_block_is_a_usable_url_not_a_storage_path(): void
    {
        $report = $this->report($this->period(2026, 1), ReportStatus::Approved, ['actual_value' => 1]);
        $report->proofs()->create([
            'path' => 'indicator-reports/1/proofs/secret-abc123.pdf',
            'original_name' => 'evidence.pdf',
            'mime' => 'application/pdf',
            'size' => 100,
            'uploaded_by' => $report->created_by,
        ]);

        $evidence = IndicatorPerformance::present($this->indicator, OutputIndicator::class)['evidence'];

        $this->assertSame('submitted', $evidence['status']);
        $this->assertSame('evidence.pdf', $evidence['label']);
        $this->assertStringNotContainsString('secret-abc123', $evidence['url']);
        $this->assertStringContainsString('/proofs/', $evidence['url']);
    }
}
