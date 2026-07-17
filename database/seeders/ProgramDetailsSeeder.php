<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

/**
 * Seeds realistic THROWAWAY strategic-program details (owner, finances, coverage)
 * so the Strategic Programs dashboard renders from the API. Idempotent-ish: only
 * fills programs whose owner is still null. Replace with real data later.
 *
 * Run: php artisan db:seed --class=ProgramDetailsSeeder
 */
class ProgramDetailsSeeder extends Seeder
{
    private const OWNERS = [
        'Livestock Extension & Business Development',
        'Ruminants & Monogastric Development',
        'Animal Health & Reproductive Services',
        'Ranch & Pastoral Resources',
        'Infrastructure, PPP & Special Programs',
    ];

    private const STATES = [
        'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno',
        'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'Gombe', 'Imo', 'Jigawa',
        'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos', 'Nasarawa', 'Niger',
        'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara', 'FCT',
    ];

    public function run(): void
    {
        $filled = 0;

        foreach (Program::whereNull('owner')->get() as $program) {
            $planned = rand(5, 50) * 500_000;                       // ₦2.5M – ₦25M
            $ratio = [0.35, 0.6, 0.85, 1.05][array_rand([0, 1, 2, 3])];
            $actual = round($planned * $ratio, 2);
            $n = rand(8, 20);
            $states = collect(self::STATES)->shuffle()->take($n)->values()->all();

            $program->update([
                'owner' => self::OWNERS[array_rand(self::OWNERS)],
                'planned_amount' => $planned,
                'actual_amount' => $actual,
                'coverage' => "{$n} states",
                'coverage_states' => $states,
            ]);
            $filled++;
        }

        $this->command->info("Seeded details for {$filled} programs.");
    }
}
