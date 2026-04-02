<?php

namespace Database\Seeders;

use App\Models\ImpactIndicator;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use Illuminate\Database\Seeder;

class IndicatorSeeder extends Seeder
{
    /**
     * Map CSV indicator_category values to the corresponding model class.
     */
    private array $categoryMap = [
        'output'  => OutputIndicator::class,
        'outcome' => OutcomeIndicator::class,
        'impact'  => ImpactIndicator::class,
    ];

    public function run(): void
    {
        $path   = base_path('Indicator Registry.csv');
        $handle = fopen($path, 'r');

        if (!$handle) {
            $this->command->error('Could not open CSV file: ' . $path);
            return;
        }

        // Skip first two metadata lines, then read the header, then skip empty line
        fgetcsv($handle);
        fgetcsv($handle);
        $header = fgetcsv($handle);
        fgetcsv($handle);

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }
            if (count($row) !== count($header)) {
                continue;
            }
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            $this->command->warn('No rows found in CSV.');
            return;
        }

        $counts   = ['output' => 0, 'outcome' => 0, 'impact' => 0, 'skipped' => 0, 'unknown' => 0];

        foreach ($rows as $row) {
            $data = array_combine($header, $row);

            // Normalize encoding
            $data = array_map(function ($value) {
                $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
                return iconv('UTF-8', 'UTF-8//IGNORE', $value);
            }, $data);

            $title = trim($data['indicator_name'] ?? '');
            if (empty($title)) {
                $counts['skipped']++;
                continue;
            }

            $categoryKey = strtolower(trim($data['indicator_category'] ?? 'output'));
            $modelClass  = $this->categoryMap[$categoryKey] ?? null;

            if (!$modelClass) {
                $this->command->warn("Unknown category '{$categoryKey}' for: {$title}");
                $counts['unknown']++;
                continue;
            }

            // Skip if a record with the same title already exists
            if ($modelClass::where('title', $title)->exists()) {
                $counts['skipped']++;
                continue;
            }

            // Create the record — code is auto-generated via model booted() event
            $modelClass::create([
                'title'            => $title,
                'description'      => trim($data['indicator_description'] ?? '') ?: null,
                'measurement_unit' => trim($data['unit_of_measure'] ?? '') ?: null,
            ]);

            $counts[$categoryKey]++;
        }

        $this->command->info(sprintf(
            'Indicators seeded — Output: %d, Outcome: %d, Impact: %d, Skipped: %d, Unknown category: %d',
            $counts['output'],
            $counts['outcome'],
            $counts['impact'],
            $counts['skipped'],
            $counts['unknown']
        ));
    }
}
