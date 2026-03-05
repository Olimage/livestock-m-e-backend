<?php

namespace Database\Seeders;

use App\Models\DisagregationCategory;
use App\Models\DisagregationItem;
use App\Models\Indicator;
use App\Models\IndicatorDisagregation;
use Illuminate\Database\Seeder;

class DisagregationSeeder extends Seeder
{
    public function run(): void
    {
        $indicators = Indicator::whereNotNull('disaggregation_dimensions')->get();

        $count = 0;

        foreach ($indicators as $indicator) {
            $dimensions = $indicator->disaggregation_dimensions;

            if (empty($dimensions) || !is_array($dimensions)) {
                continue;
            }

            foreach ($dimensions as $key => $value) {
                if (is_numeric($key)) {
                    // No subitems — value is the category name itself
                    $categoryName = (string) $value;
                    $items = [];
                } else {
                    // Has subitems — key is category name, value is array of items
                    $categoryName = (string) $key;
                    $items = is_array($value) ? $value : [(string) $value];
                }

                $categoryName = trim($categoryName);
                if (empty($categoryName)) {
                    continue;
                }

                $category = DisagregationCategory::firstOrCreate(['name' => $categoryName]);

                if (empty($items)) {
                    // Create a placeholder item matching the category name so the
                    // indicator can be linked via the pivot table.
                    $item = DisagregationItem::firstOrCreate([
                        'disagregation_category_id' => $category->id,
                        'name' => $categoryName,
                    ]);

                    IndicatorDisagregation::firstOrCreate([
                        'indicator_id' => $indicator->id,
                        'disagregation_item_id' => $item->id,
                    ]);
                } else {
                    foreach ($items as $itemName) {
                        $itemName = trim((string) $itemName);
                        if (empty($itemName)) {
                            continue;
                        }

                        $item = DisagregationItem::firstOrCreate([
                            'disagregation_category_id' => $category->id,
                            'name' => $itemName,
                        ]);

                        IndicatorDisagregation::firstOrCreate([
                            'indicator_id' => $indicator->id,
                            'disagregation_item_id' => $item->id,
                        ]);

                        $count++;
                    }
                }
            }
        }

        $categoryCount = DisagregationCategory::count();
        $itemCount = DisagregationItem::count();
        $linkCount = IndicatorDisagregation::count();

        $this->command->info("Disaggregation seeding complete.");
        $this->command->info("Categories: {$categoryCount} | Items: {$itemCount} | Indicator links: {$linkCount}");
    }
}
