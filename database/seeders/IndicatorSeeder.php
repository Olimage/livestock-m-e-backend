<?php

namespace Database\Seeders;

use App\Helper\Slugger;
use App\Models\Indicator;
use App\Models\IndicatorTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        // Skip the first two lines and the empty line after header
        fgetcsv($handle);
        fgetcsv($handle);
        $header = fgetcsv($handle);
        fgetcsv($handle); // empty line after header

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

        // Fetch all existing codes in one query
        $existingCodes = Indicator::pluck('code')->flip();

        // Pre-load indicator tiers keyed by lowercase name (e.g. 'output' => 1)
        $tierMap = IndicatorTier::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);

        $defaultTierId = $tierMap['output'] ?? $tierMap->first();

        // Track slugs used in this batch to ensure uniqueness
        $existingSlugs = Indicator::pluck('slug')->flip();
        $batchSlugs    = [];

        $now    = now()->toDateTimeString();
        $errors = 0;
        $skipped = 0;
        $batch  = [];

        foreach ($rows as $row) {
            $data = array_combine($header, $row);

            // Convert encoding
            $data = array_map(function ($value) {
                $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
                return iconv('UTF-8', 'UTF-8//IGNORE', $value);
            }, $data);

            if (empty($data['indicator_code'])) {
                continue;
            }

            // Skip existing records
            if (isset($existingCodes[$data['indicator_code']])) {
                $skipped++;
                continue;
            }

            $disagg = $this->parseDisaggregation($data['disaggregation_dimensions'] ?? null);
            $reportingFreq = $this->parseFrequency($data['reporting_frequency'] ?? '');

            $categoryKey = strtolower(trim($data['indicator_category'] ?? '') ?: 'output');
            $tierId = $tierMap[$categoryKey] ?? $defaultTierId;

            $title = $data['indicator_name'] ?? '';
            $slug = $this->generateUniqueSlug($title, $existingSlugs, $batchSlugs);
            $batchSlugs[$slug] = true;

            $batch[] = [
                'uuid'                    => (string) Str::uuid(),
                'code'                    => $data['indicator_code'],
                'title'                   => $title,
                'slug'                    => $slug,
                'description'             => $data['indicator_description'] ?? '',
                'indicator_tier_id'       => $tierId,
                'measurement_unit'        => $data['unit_of_measure'] ?? null,
                'disaggregation_dimensions' => $disagg ? json_encode($disagg) : null,
                'collection_frequency'    => $reportingFreq ? json_encode($reportingFreq) : null,
                'reporting_frequency'     => $reportingFreq ? json_encode($reportingFreq) : null,
                'created_at'              => $now,
                'updated_at'              => $now,
            ];
        }

        // Insert in chunks to avoid query size limits
        $count = 0;
        foreach (array_chunk($batch, 100) as $chunk) {
            DB::table('indicators')->insert($chunk);
            $count += count($chunk);
        }

        $this->command->info("Indicators seeded successfully! Inserted: {$count}, Skipped (existing): {$skipped}");
        if ($errors > 0) {
            $this->command->warn("Skipped rows with column mismatch: {$errors}");
        }
    }

    private function generateUniqueSlug(string $title, $existingSlugs, array &$batchSlugs): string
    {
        $base = Slugger::slugify($title, '_', true);
        $slug = $base;
        $i = 1;
        while (isset($existingSlugs[$slug]) || isset($batchSlugs[$slug])) {
            $slug = $base . '_' . $i++;
        }
        return $slug;
    }

    private function parseDisaggregation(?string $disagg): ?array
    {
        if (empty($disagg)) {
            return null;
        }

        $disaggArray = preg_split('/[\n\r•]+/', $disagg, -1, PREG_SPLIT_NO_EMPTY);
        $processed   = [];

        foreach ($disaggArray as $item) {
            $item = trim(iconv('UTF-8', 'UTF-8//IGNORE', $item), " .\t\n\r\0\v");
            if (empty($item)) {
                continue;
            }

            if (preg_match('/^(.+?)\s*\(([^)]+)\)(.*)$/', $item, $matches)) {
                $name        = strtolower(str_replace([' ', '-'], '_', preg_replace('/^by\s+/i', '', trim($matches[1]))));
                $subitems    = preg_split('/[,\n\r]+/', $matches[2], -1, PREG_SPLIT_NO_EMPTY);
                $subitems    = array_values(array_filter(array_map(function ($sub) {
                    return strtolower(trim(preg_replace('/\s*\([^)]*\)/i', '', $sub), " .\t\n\r\0\v"));
                }, $subitems)));
                $processed[$name] = $subitems;
            } else {
                $name = preg_replace('/^by\s+/i', '', $item);
                if (strpos($name, '/') !== false) {
                    foreach (preg_split('/\s*\/\s*/', $name, -1, PREG_SPLIT_NO_EMPTY) as $part) {
                        $part = strtolower(str_replace([' ', '-'], '_', trim($part)));
                        if (!empty($part)) {
                            $processed[] = $part;
                        }
                    }
                } else {
                    $name = strtolower(str_replace([' ', '-'], '_', trim($name)));
                    if (!empty($name)) {
                        $processed[] = $name;
                    }
                }
            }
        }

        return !empty($processed) ? $processed : null;
    }

    private function parseFrequency(string $reportingFreq): ?array
    {
        $reportingFreq = rtrim(str_replace('•', '', trim($reportingFreq)), '.');
        if (empty($reportingFreq)) {
            return null;
        }

        $freqArray = array_values(array_filter(array_map(
            fn($f) => trim($f, " .\t\n\r\0\v"),
            preg_split('/[\s\/,]+/', $reportingFreq, -1, PREG_SPLIT_NO_EMPTY)
        )));

        return !empty($freqArray) ? $freqArray : null;
    }
}
