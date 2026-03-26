<?php

namespace Database\Seeders;

use App\Models\DisagregationCategory;
use App\Models\DisagregationItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisagregationSeeder extends Seeder
{
    /**
     * Categories and items extracted from the Indicator Registry CSV.
     * Each key is a category name; each value is an array of item names.
     */
    private array $data = [

        'Gender' => [
            'Male',
            'Female',
        ],

        'Age Group' => [
            'Children',
            'Adolescents',
            'Youth (18–35 years)',
            'Adult (36+ years)',
        ],

        'Geographic Location' => [
            'Region/State',
            'LGA (Local Government Area)',
            'Urban',
            'Peri-Urban',
            'Rural',
            'Community',
            'Border/Crossing Point',
        ],

        'Production System' => [
            'Pastoral',
            'Agro-pastoral',
            'Semi-intensive',
            'Intensive',
            'Mixed farming',
            'Commercial',
            'Backyard/Smallholder',
            'Feedlot',
        ],

        'Livestock Species' => [
            'Cattle',
            'Goats',
            'Sheep',
            'Pigs',
            'Poultry',
            'Camels',
            'Bees',
        ],

        'Livestock Product Type' => [
            'Meat',
            'Milk/Dairy',
            'Eggs',
            'Hides and Skins',
            'Honey',
            'Leather',
            'By-products',
        ],

        'Livestock Subsector' => [
            'Meat Production',
            'Dairy Production',
            'Egg Production',
            'Honey/Apiculture',
            'Feed Manufacturing',
            'Processing',
            'Trading',
            'Related Value Chain Activities',
        ],

        'Value Chain Segment' => [
            'Production/Farming',
            'Processing',
            'Transportation',
            'Trading/Marketing',
            'Feed Manufacturing',
            'Veterinary Services',
            'Input Supply',
        ],

        'Farm/Enterprise Scale' => [
            'Smallholder',
            'Medium-scale',
            'Large-scale/Commercial',
        ],

        'Breed/Genetic Type' => [
            'Local/Indigenous',
            'Crossbreed',
            'Exotic/Improved',
        ],

        'Disease Type' => [
            'PPR (Peste des Petits Ruminants)',
            'FMD (Foot and Mouth Disease)',
            'NCD (Newcastle Disease)',
            'ASF (African Swine Fever)',
            'CBPP (Contagious Bovine Pleuropneumonia)',
            'Tick-borne diseases',
            'Trypanosomiasis',
            'Zoonoses (general)',
        ],

        'Surveillance Type' => [
            'Routine surveillance',
            'Targeted surveillance',
            'Sentinel surveillance',
        ],

        'Market Type' => [
            'Urban market',
            'Peri-urban market',
            'Rural market',
            'Formal abattoir',
            'Informal slaughter point',
            'Live bird market',
            'Livestock market',
        ],

        'Financial Institution Type' => [
            'Commercial banks',
            'Microfinance institutions',
            'Cooperatives',
            'PPP/Private investment',
        ],

        'Requesting Institution Type' => [
            'Government',
            'Research institution',
            'Private sector',
            'Development partners/NGOs',
        ],

        'Insurance Type' => [
            'Full coverage',
            'Partial coverage',
            'Indemnity-based',
        ],

        'Beekeeping System' => [
            'Traditional',
            'Transitional',
            'Modern/Commercial',
        ],

        'Conflict Type' => [
            'Farmer-herder conflict',
            'Grazing disputes',
            'Market disputes',
            'Resource competition',
        ],

        'Route Type' => [
            'Long-distance transhumance',
            'Seasonal route',
        ],

        'Facility Type' => [
            'Veterinary hospital',
            'Primary Animal Health Care Centre (PAHC)',
            'Abattoir/Slaughterhouse',
            'Veterinary diagnostic laboratory',
            'AI/Breeding centre',
            'Cold storage facility',
            'Feed mill',
        ],

        'Diagnostic Capability' => [
            'Routine testing',
            'Advanced pathology',
            'Molecular diagnostics',
        ],

        'Training/Intervention Type' => [
            'Animal husbandry',
            'Modern production techniques',
            'Alternative feedstuff',
            'Disease management',
            'Business and finance',
            'Conflict resolution/mediation',
        ],

        'Investment Type' => [
            'Production infrastructure',
            'Processing infrastructure',
            'Market infrastructure',
            'Services/Technology',
        ],
    ];

    public function run(): void
    {
        foreach ($this->data as $categoryName => $items) {
            $category = DisagregationCategory::firstOrCreate(['name' => $categoryName]);

            foreach ($items as $itemName) {
                DisagregationItem::firstOrCreate([
                    'disagregation_category_id' => $category->id,
                    'name'                      => $itemName,
                ]);
            }
        }

        $categoryCount = DisagregationCategory::count();
        $itemCount     = DisagregationItem::count();

        $this->command->info("Disaggregation seeding complete.");
        $this->command->info("Categories: {$categoryCount} | Items: {$itemCount}");
    }
}
