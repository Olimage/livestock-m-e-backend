<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Indicator;

class IndicatorSeeder extends Seeder
{
    public function run()
    {
        // ========================================
        // TIER 0: IMPACT INDICATORS (II1-II6)
        // ========================================
        
        Indicator::firstOrCreate(['code' => 'II1'], [
            'title' => 'Livestock Sector GDP Contribution',
            'description' => 'Economic Growth',
            'indicator_type' => 'impact',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'data_source' => 'National Bureau of Statistics',
            'collection_frequency' => 'Annual',
        ]);

        Indicator::firstOrCreate(['code' => 'II2'], [
            'title' => 'Daily Animal Protein Consumption Per Capita',
            'description' => 'Food Security & Nutrition',
            'indicator_type' => 'impact',
            'measurement_unit' => 'Grams/person/day',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'data_source' => 'National Nutrition Survey',
            'collection_frequency' => 'Annual',
        ]);

        Indicator::firstOrCreate(['code' => 'II3'], [
            'title' => 'Livestock-Attributable Employment',
            'description' => 'Job Creation',
            'indicator_type' => 'impact',
            'measurement_unit' => 'Number of jobs',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'data_source' => 'Labour Force Survey',
            'collection_frequency' => 'Annual',
        ]);

        Indicator::firstOrCreate(['code' => 'II4'], [
            'title' => 'Livestock Export Earnings (Foreign Exchange)',
            'description' => 'Export Performance',
            'indicator_type' => 'impact',
            'measurement_unit' => 'USD Million',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'data_source' => 'Nigeria Customs Service',
            'collection_frequency' => 'Quarterly',
        ]);

        Indicator::firstOrCreate(['code' => 'II5'], [
            'title' => 'Reduction in Livestock-Related Conflict Incidents',
            'description' => 'Peace and Security',
            'indicator_type' => 'impact',
            'measurement_unit' => 'Percentage reduction',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => 50,
            'target_year' => 2029,
            'data_source' => 'Security Agencies & NCFRMI',
            'collection_frequency' => 'Quarterly',
        ]);

        Indicator::firstOrCreate(['code' => 'II6'], [
            'title' => 'Rural Poverty Rate in Livestock Communities',
            'description' => 'Poverty Reduction',
            'indicator_type' => 'impact',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'data_source' => 'Living Standards Survey',
            'collection_frequency' => 'Biennial',
        ]);

        $this->command->info('Impact indicators (II1-II6) seeded successfully!');

        // ========================================
        // TIER 1-2: OUTCOME INDICATORS (OI1-OI32)
        // ========================================
        
        Indicator::firstOrCreate(['code' => 'OI1'], [
            'title' => 'National Livestock Population',
            'description' => 'Total count of livestock by species',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number of animals',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'National Livestock Census',
            'collection_frequency' => 'Every 5 years',
        ]);

        Indicator::firstOrCreate(['code' => 'OI2'], [
            'title' => 'Milk Production Efficiency',
            'description' => 'Liters of milk per dairy cow per year',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Liters/cow/year',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Farm Production Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI3'], [
            'title' => 'Egg Production Efficiency',
            'description' => 'Number of eggs per layer per year',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Eggs/layer/year',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Poultry Farms Records',
        ]);

        Indicator::firstOrCreate(['code' => 'OI4'], [
            'title' => 'Average Daily Weight Gain',
            'description' => 'Average daily weight gain for beef cattle and small ruminants',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Kg/day',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Farm Production Records',
        ]);

        Indicator::firstOrCreate(['code' => 'OI5'], [
            'title' => 'Livestock Mortality Rate',
            'description' => 'Annual mortality rate of livestock by species',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Veterinary Disease Surveillance',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI6'], [
            'title' => 'Vaccination Coverage Rate',
            'description' => 'Percentage of livestock vaccinated against priority diseases',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => 70,
            'target_year' => 2035,
            'data_source' => 'NVRI & Vaccination Campaign Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI7'], [
            'title' => 'Transboundary Animal Disease (TAD) Outbreak Incidence',
            'description' => 'Number of TAD outbreak incidents per year',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number of outbreaks',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => 0,
            'target_year' => 2035,
            'data_source' => 'TAD Surveillance System',
            'collection_frequency' => 'Quarterly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI8'], [
            'title' => 'Zoonotic Spillover Events',
            'description' => 'Number of zoonotic disease spillover events to humans',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number of events',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'One Health Surveillance',
            'collection_frequency' => 'Monthly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI9'], [
            'title' => 'Year-Round Feed Access',
            'description' => 'Percentage of livestock farmers with year-round feed availability',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Farm Household Survey',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI10'], [
            'title' => 'Water Points Per 1000 Animals',
            'description' => 'Number of functional water points per 1000 livestock',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number/1000 animals',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Water Infrastructure Mapping',
            'collection_frequency' => 'Biennial'
        ]);

        Indicator::firstOrCreate(['code' => 'OI11'], [
            'title' => 'Livestock Extension Worker to Farmer Ratio',
            'description' => 'Ratio of extension workers to livestock farmers',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Ratio (1:X)',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Extension Services Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI12'], [
            'title' => 'Community Animal Health Workers Deployed',
            'description' => 'Number of trained CAHWs providing services',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'CAHW Training & Deployment Records',
        ]);

        Indicator::firstOrCreate(['code' => 'OI13'], [
            'title' => 'Export Volume by Product',
            'description' => 'Volume of livestock products exported by type',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Metric Tons',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Customs Export Data',
            'collection_frequency' => 'Quarterly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI14'], [
            'title' => 'Abattoirs Meeting International Standards',
            'description' => 'Percentage of abattoirs meeting OIE/HACCP standards',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2027,
            'data_source' => 'Veterinary Inspection Reports',
        ]);

        Indicator::firstOrCreate(['code' => 'OI15'], [
            'title' => 'Animals Registered in NAITS',
            'description' => 'Percentage of commercial livestock registered in National Animal ID & Traceability System',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'NAITS Database',
            'collection_frequency' => 'Quarterly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI16'], [
            'title' => 'Infrastructure Projects Completed',
            'description' => 'Number of livestock infrastructure projects completed under PPP',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2027,
            'data_source' => 'PPP Project Database',
        ]);

        Indicator::firstOrCreate(['code' => 'OI17'], [
            'title' => 'Livestock Credit as Percentage of Agric Lending',
            'description' => 'Livestock sector credit as % of total agricultural lending',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Central Bank of Nigeria',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI18'], [
            'title' => 'Private Investment Mobilized',
            'description' => 'Value of private sector investment in livestock sector',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Naira (Billions)',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Investment Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI19'], [
            'title' => 'Livestock Insurance Uptake',
            'description' => 'Percentage of commercial farmers with livestock insurance',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Insurance Companies & NAICOM',
        ]);

        Indicator::firstOrCreate(['code' => 'OI20'], [
            'title' => 'Poverty Reduction Fund Disbursement',
            'description' => 'Amount disbursed to livestock-dependent communities',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Naira (Millions)',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Fund Management Office',
        ]);

        Indicator::firstOrCreate(['code' => 'OI21'], [
            'title' => 'Functional Conflict Management Committees',
            'description' => 'Number of functional farmer-herder conflict management committees',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'NCFRMI Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI22'], [
            'title' => 'Committee Members Trained',
            'description' => 'Number of conflict management committee members trained',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Training Records',
        ]);

        Indicator::firstOrCreate(['code' => 'OI23'], [
            'title' => 'Formalized Transhumance Routes',
            'description' => 'Number of officially designated and gazetted transhumance corridors',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Land & Grazing Reserves Dept',
        ]);

        Indicator::firstOrCreate(['code' => 'OI24'], [
            'title' => 'Security Response Time',
            'description' => 'Average time to respond to conflict alerts',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Hours',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Security Alert System',
        ]);

        Indicator::firstOrCreate(['code' => 'OI25'], [
            'title' => 'Livestock Enterprises Owned by Women/Youth',
            'description' => 'Percentage of livestock enterprises owned by women or youth',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Enterprise Registration Database',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI26'], [
            'title' => 'Income Change for Women/Youth Participants',
            'description' => 'Percentage change in household income for women/youth program participants',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Program Impact Evaluation',
        ]);

        Indicator::firstOrCreate(['code' => 'OI27'], [
            'title' => 'Women/Youth Accessing Finance',
            'description' => 'Percentage of women/youth livestock entrepreneurs accessing credit',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Financial Institutions Records',
        ]);

        Indicator::firstOrCreate(['code' => 'OI28'], [
            'title' => 'Women-Led Cottage Industries',
            'description' => 'Number of women-led livestock processing cottage industries established',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Enterprise Registry',
        ]);

        Indicator::firstOrCreate(['code' => 'OI29'], [
            'title' => 'NLIMS Coverage Rate',
            'description' => 'Percentage of states reporting data through NLIMS',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => 100,
            'target_year' => 2035,
            'data_source' => 'NLIMS System Dashboard',
            'collection_frequency' => 'Quarterly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI30'], [
            'title' => 'Data Publication Timeliness',
            'description' => 'Percentage of reports published within agreed timelines',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Publication Schedule Tracker',
        ]);

        Indicator::firstOrCreate(['code' => 'OI31'], [
            'title' => 'Data Utilization in Planning',
            'description' => 'Number of policy documents citing NLIMS data',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'Policy Document Review',
        ]);

        Indicator::firstOrCreate(['code' => 'OI32'], [
            'title' => 'Database Completeness',
            'description' => 'Percentage of required data fields populated in NLIMS',
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'data_source' => 'NLIMS Data Quality Reports',
        ]);

        $this->command->info('Outcome indicators (OI1-OI32) seeded successfully!');
    }
}
