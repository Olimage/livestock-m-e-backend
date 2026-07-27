<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\OutputIndicator;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DEMO DATA — not production mapping.
 *
 * Department-scoped reporting needs both halves of the relationship populated:
 * a user must belong to a department (`user_departments`), and an indicator must
 * name its owning department (`{type}_indicators.department_id`). Both were
 * entirely empty, which is why the Director "Create Report" form listed all 75
 * indicators and showed the "Assigned department" placeholder.
 *
 * This seeder wires up one director and a handful of plausible indicators so the
 * flow is demonstrable end to end. The real mapping of all indicators to
 * departments is ministry domain work and should be done through the existing
 * admin screens (Result Chain > *Indicators > Edit, and Users > Edit).
 *
 * Idempotent, and deliberately non-destructive: it never overwrites a
 * department_id that has already been set.
 *
 * Run with: php artisan db:seed --class=DemoDepartmentAssignmentSeeder
 */
class DemoDepartmentAssignmentSeeder extends Seeder
{
    private const DEPARTMENT_SLUG = 'ruminants_monogastric_development';

    private const DIRECTOR_EMAIL = 'director@fmld.gov';

    /** Output indicators that plausibly sit with Ruminants & Monogastric Development. */
    private const INDICATOR_CODES = ['OPT-9', 'OPT-10', 'OPT-11', 'OPT-14', 'OPT-32'];

    public function run(): void
    {
        $department = Department::where('slug', self::DEPARTMENT_SLUG)->first();

        if (! $department) {
            $this->command?->warn('Department '.self::DEPARTMENT_SLUG.' not found — skipping.');

            return;
        }

        $director = User::where('email', self::DIRECTOR_EMAIL)->first();

        if ($director) {
            $director->departments()->syncWithoutDetaching([$department->id]);
            $this->command?->info("Attached {$director->email} to {$department->name}.");
        } else {
            $this->command?->warn(self::DIRECTOR_EMAIL.' not found — skipping user assignment.');
        }

        $assigned = OutputIndicator::whereIn('code', self::INDICATOR_CODES)
            ->whereNull('department_id')
            ->update(['department_id' => $department->id]);

        $this->command?->info("Assigned {$assigned} output indicator(s) to {$department->name}.");
    }
}
