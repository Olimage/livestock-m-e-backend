<?php

namespace Database\Seeders;

use App\Enums\ReportStatus;
use App\Models\ActivityLog;
use App\Models\BondDeliverable;
use App\Models\BondOutputIndicator;
use App\Models\Department;
use App\Models\DisagregationCategory;
use App\Models\ImpactIndicator;
use App\Models\IndicatorReport;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use App\Models\ReportingPeriod;
use App\Models\SectoralGoal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds realistic THROWAWAY sample reporting data so the FMLD dashboards can be
 * built and demoed end-to-end before real M&E data entry exists.
 *
 * Populates: bond output indicators (+ deliverable links), indicator reports
 * (target/actual/status per period), disaggregated report values, evidence
 * proofs, and an activity log. Idempotent — safe to run more than once.
 *
 * Run: php artisan db:seed --class=DashboardSampleSeeder
 *
 * This data is scaffolding; it is replaced by the real reporting pipeline later.
 */
class DashboardSampleSeeder extends Seeder
{
    /** Indicator types that support disaggregation (have a pivot table). */
    private const DISAGG_PIVOT = [
        OutcomeIndicator::class => ['outcome_indicator_disaggregation', 'outcome_indicator_id'],
        OutputIndicator::class => ['output_indicator_disaggregation', 'output_indicator_id'],
        ImpactIndicator::class => ['impact_indicator_disaggregation', 'impact_indicator_id'],
    ];

    private const NARRATIVES = [
        'Progress on track against the quarterly target; field verification completed.',
        'Slower uptake than planned due to delayed disbursement; recovery expected next quarter.',
        'Target exceeded following expanded state coverage and partner support.',
        'Steady performance; data validated against state-level submissions.',
        'Below target owing to logistics constraints in northern states.',
    ];

    public function run(): void
    {
        $user = User::first();
        if (! $user) {
            $this->command->warn('DashboardSampleSeeder: no users found — aborting.');

            return;
        }

        $fallbackDeptId = optional(Department::first())->id;
        if (! $fallbackDeptId) {
            $this->command->warn('DashboardSampleSeeder: no departments found — aborting.');

            return;
        }

        // Use the first three quarters as "reported" periods; leave Q4 unreported.
        $periods = ReportingPeriod::orderBy('id')->take(3)->get();
        if ($periods->isEmpty()) {
            $this->command->warn('DashboardSampleSeeder: no reporting periods found — aborting.');

            return;
        }

        $this->linkResultChain();

        $this->seedBondOutputIndicators();
        $bois = BondOutputIndicator::orderBy('id')->get();
        $this->linkBondIndicatorsToDeliverables($bois);

        // Two categories' items make the disaggregation breakdown (e.g. Gender + Age Group).
        $categories = DisagregationCategory::with('items')->orderBy('id')->take(2)->get();
        $disaggItemIds = $categories->flatMap(fn ($c) => $c->items->take(6))->pluck('id')->all();

        $types = [
            OutcomeIndicator::class,
            OutputIndicator::class,
            ImpactIndicator::class,
            BondOutputIndicator::class,
        ];

        $reportsCreated = 0;

        foreach ($types as $class) {
            foreach ($class::orderBy('id')->get() as $indicator) {
                $itemIds = $this->linkDisaggregation($class, $indicator->id, $disaggItemIds);
                $deptId = $indicator->getAttribute('department_id') ?: $fallbackDeptId;

                foreach ($periods as $index => $period) {
                    $already = IndicatorReport::where('indicator_type', $class)
                        ->where('indicator_id', $indicator->id)
                        ->where('reporting_period_id', $period->id)
                        ->exists();
                    if ($already) {
                        continue;
                    }

                    [$target, $actual] = $this->targetActual($index);

                    $report = IndicatorReport::create([
                        'indicator_type' => $class,
                        'indicator_id' => $indicator->id,
                        'indicator_code' => $indicator->code ?? null,
                        'department_id' => $deptId,
                        'reporting_period_id' => $period->id,
                        'target_value' => $target,
                        'actual_value' => $actual,
                        'narrative' => self::NARRATIVES[array_rand(self::NARRATIVES)],
                        'status' => ReportStatus::Approved,
                        'created_by' => $user->id,
                        'submitted_at' => now()->subDays(30 - $index * 5),
                        'published_at' => now()->subDays(25 - $index * 5),
                    ]);

                    $this->seedValues($report->id, $itemIds, $actual);
                    $this->maybeSeedProof($report->id, $user->id);
                    $reportsCreated++;
                }
            }
        }

        $this->seedActivityLog($user->id);

        $this->command->info("DashboardSampleSeeder: created {$reportsCreated} indicator reports (+ values, proofs).");
    }

    /**
     * Populate the result-chain associations the dashboards traverse:
     * outcome indicators → sectoral goals, and output indicators → outcome indicators.
     * Idempotent (updateOrInsert). Distributes indicators round-robin.
     */
    private function linkResultChain(): void
    {
        $goalIds = SectoralGoal::orderBy('id')->pluck('id')->all();
        if (! empty($goalIds)) {
            foreach (OutcomeIndicator::orderBy('id')->get()->values() as $i => $outcome) {
                DB::table('outcome_indicator_sectoral_goal')->updateOrInsert(
                    ['outcome_indicator_id' => $outcome->id, 'sectoral_goal_id' => $goalIds[$i % count($goalIds)]],
                    []
                );
            }
        }

        $outcomeIds = OutcomeIndicator::orderBy('id')->pluck('id')->all();
        if (! empty($outcomeIds)) {
            foreach (OutputIndicator::orderBy('id')->get()->values() as $i => $output) {
                DB::table('output_indicator_outcome_indicator')->updateOrInsert(
                    ['output_indicator_id' => $output->id, 'outcome_indicator_id' => $outcomeIds[$i % count($outcomeIds)]],
                    []
                );
            }
        }
    }

    private function seedBondOutputIndicators(): void
    {
        if (BondOutputIndicator::count() > 0) {
            return;
        }

        $titles = [
            'Number of breeding centres supported for cross/selective breeding',
            'Number of artificial insemination centres supported',
            'Number of feed mills rehabilitated and operational',
            'Number of animal health workers (CAHWs) trained and deployed',
            'Number of vaccination campaigns conducted',
            'Number of livestock markets upgraded',
            'Number of cooperatives registered and active',
            'Number of hectares of grazing reserves rehabilitated',
        ];

        foreach ($titles as $title) {
            // code is auto-set to "BOI-{id}" by the model's booted() hook.
            BondOutputIndicator::create(['title' => $title]);
        }
    }

    private function linkBondIndicatorsToDeliverables($bois): void
    {
        if ($bois->isEmpty()) {
            return;
        }

        $count = $bois->count();
        foreach (BondDeliverable::orderBy('id')->get()->values() as $i => $deliverable) {
            $attach = $bois->slice(($i * 2) % $count, 3)->pluck('id')->all();
            if (count($attach) < 2) {
                $attach = $bois->take(3)->pluck('id')->all();
            }
            $deliverable->bondOutputIndicators()->syncWithoutDetaching($attach);
        }
    }

    /**
     * Link an indicator to disaggregation items (idempotent) and return the item ids.
     *
     * @return array<int, int>
     */
    private function linkDisaggregation(string $class, int $indicatorId, array $disaggItemIds): array
    {
        if (! isset(self::DISAGG_PIVOT[$class]) || empty($disaggItemIds)) {
            return [];
        }

        [$pivot, $column] = self::DISAGG_PIVOT[$class];

        foreach ($disaggItemIds as $itemId) {
            DB::table($pivot)->updateOrInsert(
                [$column => $indicatorId, 'disagregation_item_id' => $itemId],
                []
            );
        }

        return $disaggItemIds;
    }

    /**
     * @return array{0: float, 1: float} [target, actual]
     */
    private function targetActual(int $periodIndex): array
    {
        $target = (float) rand(100, 1000);
        // Spread across below/on/above target for a realistic status mix.
        $factor = [0.62, 0.85, 1.12, 0.95, 1.30][array_rand([0, 1, 2, 3, 4])];
        $actual = round($target * $factor, 2);

        return [$target, $actual];
    }

    private function seedValues(int $reportId, array $itemIds, float $actual): void
    {
        if (empty($itemIds)) {
            return;
        }

        $per = round($actual / count($itemIds), 2);
        $rows = [];
        foreach ($itemIds as $itemId) {
            $rows[] = [
                'report_id' => $reportId,
                'disagregation_item_id' => $itemId,
                'value' => max(0, round($per * (rand(70, 130) / 100), 2)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('indicator_report_values')->insert($rows);
    }

    private function maybeSeedProof(int $reportId, int $userId): void
    {
        if (rand(1, 10) > 4) {
            return; // ~40% of reports carry evidence
        }

        DB::table('indicator_report_proofs')->insert([
            'report_id' => $reportId,
            'path' => "proofs/sample-{$reportId}.pdf",
            'original_name' => 'evidence-report.pdf',
            'mime' => 'application/pdf',
            'size' => rand(50_000, 500_000),
            'uploaded_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedActivityLog(int $userId): void
    {
        if (ActivityLog::count() > 0) {
            return;
        }

        $entries = [
            ['action' => 'Update', 'description' => 'Updated 156 cattle vaccination entries', 'db_action' => 'updated'],
            ['action' => 'Create', 'description' => 'Submitted Q2 outcome indicator report', 'db_action' => 'created'],
            ['action' => 'Delete', 'description' => 'Removed 47 duplicate registration records', 'db_action' => 'deleted'],
            ['action' => 'Approve', 'description' => 'Approved bond deliverable report', 'db_action' => 'updated'],
            ['action' => 'Update', 'description' => 'Revised target values for breeding centres', 'db_action' => 'updated'],
            ['action' => 'Create', 'description' => 'Uploaded evidence proof for AI centres', 'db_action' => 'created'],
        ];

        $rows = [];
        foreach ($entries as $i => $entry) {
            $rows[] = [
                'user_id' => $userId,
                'action' => $entry['action'],
                'db_action' => $entry['db_action'],
                'description' => $entry['description'],
                'method' => 'POST',
                'url' => '/api/v1/indicator-reports',
                'ip_address' => '127.0.0.1',
                'status_code' => 200,
                'created_at' => now()->subHours($i * 3),
                'updated_at' => now()->subHours($i * 3),
            ];
        }
        DB::table('activity_logs')->insert($rows);
    }
}
