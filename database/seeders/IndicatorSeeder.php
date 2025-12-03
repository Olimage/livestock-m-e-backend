<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Indicator;
use App\Models\PresidentialPriority;
use App\Models\SectoralGoal;
use App\Models\BondOutcome;
use App\Models\NlgasPillar;
use App\Models\Department;

class IndicatorSeeder extends Seeder
{
    public function run()
    {
        // Helper functions
        $pp = fn(string $code) => PresidentialPriority::where('code', $code)->first()?->id;
        $sg = fn(string $code) => SectoralGoal::where('code', $code)->first()?->id;
        $bo = fn(string $code) => BondOutcome::where('code', $code)->first()?->id;
        $p = fn(string $code) => NlgasPillar::where('code', $code)->first()?->id;
        $dept = fn(string $slug) => Department::where('slug', $slug)->first()?->id;

        // ========================================
        // TIER 0: IMPACT INDICATORS (II1-II6)
        // ========================================
        
        Indicator::firstOrCreate(['code' => 'II1'], [
            'title' => 'Livestock Sector GDP Contribution',
            'description' => 'Percentage contribution of livestock sector to national GDP',
            'presidential_priority_id' => $pp('PP2'),
            'indicator_type' => 'impact',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'tier_level' => 0,
            'data_source' => 'National Bureau of Statistics',
            'collection_frequency' => 'Annual',
            'responsible_entity' => 'FMLD Planning Department'
        ]);

        Indicator::firstOrCreate(['code' => 'II2'], [
            'title' => 'Daily Animal Protein Consumption Per Capita',
            'description' => 'Grams of animal protein consumed per person per day',
            'presidential_priority_id' => $pp('PP1'),
            'indicator_type' => 'impact',
            'measurement_unit' => 'Grams/person/day',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'tier_level' => 0,
            'data_source' => 'National Nutrition Survey',
            'collection_frequency' => 'Annual',
            'responsible_entity' => 'FMLD & Ministry of Health'
        ]);

        Indicator::firstOrCreate(['code' => 'II3'], [
            'title' => 'Livestock-Attributable Employment',
            'description' => 'Number of jobs created or sustained in livestock value chain',
            'presidential_priority_id' => $pp('PP4'),
            'indicator_type' => 'impact',
            'measurement_unit' => 'Number of jobs',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'tier_level' => 0,
            'data_source' => 'Labour Force Survey',
            'collection_frequency' => 'Annual',
            'responsible_entity' => 'FMLD Planning & NBS'
        ]);

        Indicator::firstOrCreate(['code' => 'II4'], [
            'title' => 'Livestock Export Earnings (Foreign Exchange)',
            'description' => 'Total foreign exchange earned from livestock product exports',
            'presidential_priority_id' => $pp('PP2'),
            'indicator_type' => 'impact',
            'measurement_unit' => 'USD Million',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'tier_level' => 0,
            'data_source' => 'Nigeria Customs Service',
            'collection_frequency' => 'Quarterly',
            'responsible_entity' => 'FMLD Export Promotion'
        ]);

        Indicator::firstOrCreate(['code' => 'II5'], [
            'title' => 'Reduction in Livestock-Related Conflict Incidents',
            'description' => 'Percentage reduction in farmer-herder conflict incidents',
            'presidential_priority_id' => $pp('PP3'),
            'indicator_type' => 'impact',
            'measurement_unit' => 'Percentage reduction',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => 50,
            'target_year' => 2029,
            'tier_level' => 0,
            'data_source' => 'Security Agencies & NCFRMI',
            'collection_frequency' => 'Quarterly',
            'responsible_entity' => 'National Centre for the Control of Small Arms'
        ]);

        Indicator::firstOrCreate(['code' => 'II6'], [
            'title' => 'Rural Poverty Rate in Livestock Communities',
            'description' => 'Percentage of livestock-dependent households below poverty line',
            'presidential_priority_id' => $pp('PP2'),
            'indicator_type' => 'impact',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2029,
            'tier_level' => 0,
            'data_source' => 'Living Standards Survey',
            'collection_frequency' => 'Biennial',
            'responsible_entity' => 'National Bureau of Statistics'
        ]);

        $this->command->info('Impact indicators (II1-II6) seeded successfully!');

        // ========================================
        // TIER 1-2: OUTCOME INDICATORS (OI1-OI32)
        // ========================================
        
        Indicator::firstOrCreate(['code' => 'OI1'], [
            'title' => 'National Livestock Population',
            'description' => 'Total count of livestock by species',
            'sectoral_goal_id' => $sg('SG1'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P1'),
            'department_id' => $dept('ruminants_monogastric_development'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number of animals',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'National Livestock Census',
            'collection_frequency' => 'Every 5 years',
            'responsible_entity' => 'Dept of Production'
        ]);

        Indicator::firstOrCreate(['code' => 'OI2'], [
            'title' => 'Milk Production Efficiency',
            'description' => 'Liters of milk per dairy cow per year',
            'sectoral_goal_id' => $sg('SG1'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P1'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Liters/cow/year',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Farm Production Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI3'], [
            'title' => 'Egg Production Efficiency',
            'description' => 'Number of eggs per layer per year',
            'sectoral_goal_id' => $sg('SG1'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P1'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Eggs/layer/year',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Poultry Farms Records'
        ]);

        Indicator::firstOrCreate(['code' => 'OI4'], [
            'title' => 'Average Daily Weight Gain',
            'description' => 'Average daily weight gain for beef cattle and small ruminants',
            'sectoral_goal_id' => $sg('SG1'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P1'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Kg/day',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Farm Production Records'
        ]);

        Indicator::firstOrCreate(['code' => 'OI5'], [
            'title' => 'Livestock Mortality Rate',
            'description' => 'Annual mortality rate of livestock by species',
            'sectoral_goal_id' => $sg('SG2'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P2'),
            'department_id' => $dept('animal_health_reproductive_services'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Veterinary Disease Surveillance',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI6'], [
            'title' => 'Vaccination Coverage Rate',
            'description' => 'Percentage of livestock vaccinated against priority diseases',
            'sectoral_goal_id' => $sg('SG2'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P2'),
            'department_id' => $dept('animal_health_reproductive_services'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => 70,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'NVRI & Vaccination Campaign Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI7'], [
            'title' => 'Transboundary Animal Disease (TAD) Outbreak Incidence',
            'description' => 'Number of TAD outbreak incidents per year',
            'sectoral_goal_id' => $sg('SG2'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P2'),
            'department_id' => $dept('transboundary_animal_disease'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number of outbreaks',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => 0,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'TAD Surveillance System',
            'collection_frequency' => 'Quarterly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI8'], [
            'title' => 'Zoonotic Spillover Events',
            'description' => 'Number of zoonotic disease spillover events to humans',
            'sectoral_goal_id' => $sg('SG2'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P2'),
            'department_id' => $dept('veterinary_public_health_epidemiology'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number of events',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'One Health Surveillance',
            'collection_frequency' => 'Monthly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI9'], [
            'title' => 'Year-Round Feed Access',
            'description' => 'Percentage of livestock farmers with year-round feed availability',
            'sectoral_goal_id' => $sg('SG3'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P3'),
            'department_id' => $dept('ranch_pastoral_resources_development'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Farm Household Survey',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI10'], [
            'title' => 'Water Points Per 1000 Animals',
            'description' => 'Number of functional water points per 1000 livestock',
            'sectoral_goal_id' => $sg('SG3'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P4'),
            'department_id' => $dept('ranch_pastoral_resources_development'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number/1000 animals',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Water Infrastructure Mapping',
            'collection_frequency' => 'Biennial'
        ]);

        Indicator::firstOrCreate(['code' => 'OI11'], [
            'title' => 'Livestock Extension Worker to Farmer Ratio',
            'description' => 'Ratio of extension workers to livestock farmers',
            'sectoral_goal_id' => $sg('SG3'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P8'),
            'department_id' => $dept('livestock_extension_business_development'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Ratio (1:X)',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Extension Services Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI12'], [
            'title' => 'Community Animal Health Workers Deployed',
            'description' => 'Number of trained CAHWs providing services',
            'sectoral_goal_id' => $sg('SG3'),
            'bond_outcome_id' => $bo('BO1'),
            'nlgas_pillar_id' => $p('P8'),
            'department_id' => $dept('animal_health_reproductive_services'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'CAHW Training & Deployment Records'
        ]);

        Indicator::firstOrCreate(['code' => 'OI13'], [
            'title' => 'Export Volume by Product',
            'description' => 'Volume of livestock products exported by type',
            'sectoral_goal_id' => $sg('SG4'),
            'bond_outcome_id' => $bo('BO3'),
            'nlgas_pillar_id' => $p('P1'),
            'department_id' => $dept('livestock_extension_business_development'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Metric Tons',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Customs Export Data',
            'collection_frequency' => 'Quarterly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI14'], [
            'title' => 'Abattoirs Meeting International Standards',
            'description' => 'Percentage of abattoirs meeting OIE/HACCP standards',
            'sectoral_goal_id' => $sg('SG4'),
            'bond_outcome_id' => $bo('BO2'),
            'nlgas_pillar_id' => $p('P7'),
            'department_id' => $dept('quality_assurance_certification'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2027,
            'tier_level' => 2,
            'data_source' => 'Veterinary Inspection Reports'
        ]);

        Indicator::firstOrCreate(['code' => 'OI15'], [
            'title' => 'Animals Registered in NAITS',
            'description' => 'Percentage of commercial livestock registered in National Animal ID & Traceability System',
            'sectoral_goal_id' => $sg('SG8'),
            'bond_outcome_id' => $bo('BO3'),
            'nlgas_pillar_id' => $p('P10'),
            'department_id' => $dept('planning_research_statistics'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'NAITS Database',
            'collection_frequency' => 'Quarterly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI16'], [
            'title' => 'Infrastructure Projects Completed',
            'description' => 'Number of livestock infrastructure projects completed under PPP',
            'sectoral_goal_id' => $sg('SG4'),
            'bond_outcome_id' => $bo('BO2'),
            'nlgas_pillar_id' => $p('P7'),
            'department_id' => $dept('procurement'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2027,
            'tier_level' => 2,
            'data_source' => 'PPP Project Database'
        ]);

        Indicator::firstOrCreate(['code' => 'OI17'], [
            'title' => 'Livestock Credit as Percentage of Agric Lending',
            'description' => 'Livestock sector credit as % of total agricultural lending',
            'sectoral_goal_id' => $sg('SG5'),
            'bond_outcome_id' => $bo('BO2'),
            'nlgas_pillar_id' => $p('P5'),
            'department_id' => $dept('finance_and_accounts'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Central Bank of Nigeria',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI18'], [
            'title' => 'Private Investment Mobilized',
            'description' => 'Value of private sector investment in livestock sector',
            'sectoral_goal_id' => $sg('SG5'),
            'bond_outcome_id' => $bo('BO2'),
            'nlgas_pillar_id' => $p('P5'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Naira (Billions)',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Investment Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI19'], [
            'title' => 'Livestock Insurance Uptake',
            'description' => 'Percentage of commercial farmers with livestock insurance',
            'sectoral_goal_id' => $sg('SG5'),
            'bond_outcome_id' => $bo('BO2'),
            'nlgas_pillar_id' => $p('P5'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'Insurance Companies & NAICOM'
        ]);

        Indicator::firstOrCreate(['code' => 'OI20'], [
            'title' => 'Poverty Reduction Fund Disbursement',
            'description' => 'Amount disbursed to livestock-dependent communities',
            'sectoral_goal_id' => $sg('SG5'),
            'bond_outcome_id' => $bo('BO2'),
            'nlgas_pillar_id' => $p('P5'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Naira (Millions)',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'Fund Management Office'
        ]);

        Indicator::firstOrCreate(['code' => 'OI21'], [
            'title' => 'Functional Conflict Management Committees',
            'description' => 'Number of functional farmer-herder conflict management committees',
            'sectoral_goal_id' => $sg('SG6'),
            'bond_outcome_id' => $bo('BO4'),
            'nlgas_pillar_id' => $p('P6'),
            'department_id' => $dept('ranch_pastoral_resources_development'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'NCFRMI Records',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI22'], [
            'title' => 'Committee Members Trained',
            'description' => 'Number of conflict management committee members trained',
            'sectoral_goal_id' => $sg('SG6'),
            'bond_outcome_id' => $bo('BO4'),
            'nlgas_pillar_id' => $p('P6'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'Training Records'
        ]);

        Indicator::firstOrCreate(['code' => 'OI23'], [
            'title' => 'Formalized Transhumance Routes',
            'description' => 'Number of officially designated and gazetted transhumance corridors',
            'sectoral_goal_id' => $sg('SG6'),
            'bond_outcome_id' => $bo('BO4'),
            'nlgas_pillar_id' => $p('P6'),
            'department_id' => $dept('ranch_pastoral_resources_development'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Land & Grazing Reserves Dept'
        ]);

        Indicator::firstOrCreate(['code' => 'OI24'], [
            'title' => 'Security Response Time',
            'description' => 'Average time to respond to conflict alerts',
            'sectoral_goal_id' => $sg('SG6'),
            'bond_outcome_id' => $bo('BO4'),
            'nlgas_pillar_id' => $p('P6'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Hours',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'Security Alert System'
        ]);

        Indicator::firstOrCreate(['code' => 'OI25'], [
            'title' => 'Livestock Enterprises Owned by Women/Youth',
            'description' => 'Percentage of livestock enterprises owned by women or youth',
            'sectoral_goal_id' => $sg('SG7'),
            'bond_outcome_id' => $bo('BO5'),
            'nlgas_pillar_id' => $p('P9'),
            'department_id' => $dept('gender_youth_empowerment'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'Enterprise Registration Database',
            'collection_frequency' => 'Annual'
        ]);

        Indicator::firstOrCreate(['code' => 'OI26'], [
            'title' => 'Income Change for Women/Youth Participants',
            'description' => 'Percentage change in household income for women/youth program participants',
            'sectoral_goal_id' => $sg('SG7'),
            'bond_outcome_id' => $bo('BO5'),
            'nlgas_pillar_id' => $p('P9'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'Program Impact Evaluation'
        ]);

        Indicator::firstOrCreate(['code' => 'OI27'], [
            'title' => 'Women/Youth Accessing Finance',
            'description' => 'Percentage of women/youth livestock entrepreneurs accessing credit',
            'sectoral_goal_id' => $sg('SG7'),
            'bond_outcome_id' => $bo('BO5'),
            'nlgas_pillar_id' => $p('P9'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'Financial Institutions Records'
        ]);

        Indicator::firstOrCreate(['code' => 'OI28'], [
            'title' => 'Women-Led Cottage Industries',
            'description' => 'Number of women-led livestock processing cottage industries established',
            'sectoral_goal_id' => $sg('SG7'),
            'bond_outcome_id' => $bo('BO5'),
            'nlgas_pillar_id' => $p('P9'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'Enterprise Registry'
        ]);

        Indicator::firstOrCreate(['code' => 'OI29'], [
            'title' => 'NLIMS Coverage Rate',
            'description' => 'Percentage of states reporting data through NLIMS',
            'sectoral_goal_id' => $sg('SG8'),
            'bond_outcome_id' => $bo('BO5'),
            'nlgas_pillar_id' => $p('P10'),
            'department_id' => $dept('planning_research_statistics'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => 100,
            'target_year' => 2035,
            'tier_level' => 1,
            'data_source' => 'NLIMS System Dashboard',
            'collection_frequency' => 'Quarterly'
        ]);

        Indicator::firstOrCreate(['code' => 'OI30'], [
            'title' => 'Data Publication Timeliness',
            'description' => 'Percentage of reports published within agreed timelines',
            'sectoral_goal_id' => $sg('SG8'),
            'bond_outcome_id' => $bo('BO7'),
            'nlgas_pillar_id' => $p('P10'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'Publication Schedule Tracker'
        ]);

        Indicator::firstOrCreate(['code' => 'OI31'], [
            'title' => 'Data Utilization in Planning',
            'description' => 'Number of policy documents citing NLIMS data',
            'sectoral_goal_id' => $sg('SG8'),
            'bond_outcome_id' => $bo('BO7'),
            'nlgas_pillar_id' => $p('P10'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Number',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'Policy Document Review'
        ]);

        Indicator::firstOrCreate(['code' => 'OI32'], [
            'title' => 'Database Completeness',
            'description' => 'Percentage of required data fields populated in NLIMS',
            'sectoral_goal_id' => $sg('SG8'),
            'bond_outcome_id' => $bo('BO8'),
            'nlgas_pillar_id' => $p('P10'),
            'indicator_type' => 'outcome',
            'measurement_unit' => 'Percentage',
            'baseline_value' => null,
            'baseline_year' => 2024,
            'target_value' => null,
            'target_year' => 2035,
            'tier_level' => 2,
            'data_source' => 'NLIMS Data Quality Reports'
        ]);

        $this->command->info('Outcome indicators (OI1-OI32) seeded successfully!');
    }
}
