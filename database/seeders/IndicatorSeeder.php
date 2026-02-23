<?php

namespace Database\Seeders;

use App\Models\Indicator;
use Illuminate\Database\Seeder;

class IndicatorSeeder extends Seeder
{
    public function run()
    {
        $path = base_path('Indicator Registry.csv');
        $handle = fopen($path, 'r');

        if (!$handle) {
            $this->command->error('Could not open CSV file: ' . $path);
            return;
        }

        // Skip the first two lines
        fgetcsv($handle);
        fgetcsv($handle);

        // Read header
        $header = fgetcsv($handle);

        // Skip the empty line after header
        fgetcsv($handle);

        $count = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Ensure we have the right number of columns
            if (count($row) !== count($header)) {
                $errors++;
                continue;
            }

            $data = array_combine($header, $row);

            // Convert encoding to UTF-8 to handle Windows-1252 characters
            $data = array_map(function ($value) {
                $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
                // Clean up any invalid UTF-8 characters
                $value = iconv('UTF-8', 'UTF-8//IGNORE', $value);
                return $value;
            }, $data);

            // Skip if no code
            if (empty($data['indicator_code'])) {
                continue;
            }

            $disagg = $data['disaggregation_dimensions'] ?? null;
            if ($disagg) {
                // Split by newlines and bullet points
                $disaggArray = preg_split('/[\n\r•]+/', $disagg, -1, PREG_SPLIT_NO_EMPTY);

                $processed = [];

                foreach ($disaggArray as $item) {
                    $item = trim($item, " .\t\n\r\0\v");
                    $item = iconv('UTF-8', 'UTF-8//IGNORE', $item);

                    if (empty($item)) {
                        continue;
                    }

                    // Check if item has parentheses for subitems
                    if (preg_match('/^(.+?)\s*\(([^)]+)\)(.*)$/', $item, $matches)) {
                        $name = trim($matches[1]);
                        $subitemsStr = $matches[2];

                        // Normalize the name: remove "By " prefix and convert to snake_case
                        $name = preg_replace('/^by\s+/i', '', $name);
                        $name = trim($name);
                        $name = strtolower($name);
                        $name = str_replace([' ', '-'], '_', $name);

                        // Parse subitems
                        $subitems = preg_split('/[,\n\r]+/', $subitemsStr, -1, PREG_SPLIT_NO_EMPTY);
                        $subitems = array_map(function ($sub) {
                            $sub = trim($sub, " .\t\n\r\0\v");
                            // Remove nested parenthetical descriptions
                            $sub = preg_replace('/\s*\([^)]*\)/i', '', $sub);
                            $sub = strtolower(trim($sub));
                            return trim($sub);
                        }, $subitems);
                        $subitems = array_filter($subitems);

                        // Add as associative array element with string key
                        $processed[$name] = array_values($subitems);
                    } else {
                        // No parentheses - normalize and check for splitting
                        $name = preg_replace('/^by\s+/i', '', $item);
                        $name = trim($name);

                        // Check if name has "/" for splitting into multiple items
                        if (strpos($name, '/') !== false) {
                            $parts = preg_split('/\s*\/\s*/', $name, -1, PREG_SPLIT_NO_EMPTY);
                            foreach ($parts as $part) {
                                $part = trim($part);
                                $part = strtolower($part);
                                $part = str_replace([' ', '-'], '_', $part);
                                if (!empty($part)) {
                                    $processed[] = $part;  // Indexed element
                                }
                            }
                        } else {
                            $name = strtolower($name);
                            $name = str_replace([' ', '-'], '_', $name);
                            if (!empty($name)) {
                                $processed[] = $name;  // Indexed element
                            }
                        }
                    }
                }

                $disagg = !empty($processed) ? $processed : null;
            } else {
                $disagg = null;
            }

            $reportingFreq = trim($data['reporting_frequency'] ?? '');
            if (!empty($reportingFreq)) {
                // Remove bullet points and extra punctuation
                $reportingFreq = str_replace('•', '', $reportingFreq);
                $reportingFreq = rtrim($reportingFreq, '.');

                // Split by forward slash or comma to create array
                $freqArray = preg_split('/[\s\/,]+/', $reportingFreq, -1, PREG_SPLIT_NO_EMPTY);

                // Trim, clean, and filter each value
                $freqArray = array_map(function ($freq) {
                    return trim($freq, " .\t\n\r\0\v");
                }, $freqArray);

                $freqArray = array_filter($freqArray);
                $reportingFreq = !empty($freqArray) ? array_values($freqArray) : null;
            } else {
                $reportingFreq = null;
            }

            $category = trim($data['indicator_category'] ?? '');
            $category = $category ?: 'output';

            $indicatorData = [
                'code' => $data['indicator_code'],
                'title' => $data['indicator_name'] ?? '',
                'description' => $data['indicator_description'] ?? '',
                'indicator_type' => strtolower($category),
                'measurement_unit' => $data['unit_of_measure'] ?? null,
                'disaggregation_dimensions' => $disagg,
                'collection_frequency' => $reportingFreq,
                // 'baseline_value' => !empty($data['baseline_value']) ? (float) $data['baseline_value'] : null,
                // 'baseline_year' => !empty($data['baseline_year']) ? (int) $data['baseline_year'] : null,
                // 'target_value' => !empty($data['target_value']) ? (float) $data['target_value'] : null,
                // 'target_year' => !empty($data['target_year']) ? (int) $data['target_year'] : null,
                'data_source' => $data['data_source_entities'] ?? null,
            ];

            Indicator::firstOrCreate(['code' => $indicatorData['code']], $indicatorData);
            $count++;
        }

        fclose($handle);

        $this->command->info("Indicators seeded successfully! Total: {$count}");
        if ($errors > 0) {
            $this->command->warn("Skipped rows with column mismatch: {$errors}");
        }
    }
}
