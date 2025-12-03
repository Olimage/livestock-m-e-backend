<?php
namespace Database\Seeders;

use App\Models\BondOutcome;
use App\Models\CrossCuttingMetric;
use App\Models\Department;
use App\Models\Indicator;
use App\Models\NlgasPillar;
use App\Models\PresidentialPriority;
use App\Models\Program;
use App\Models\SectoralGoal;
use App\Models\StrategicAlignment;
use Illuminate\Database\Seeder;

class ComprehensiveStrategicAlignmentSeeder extends Seeder
{
    public function run()
    {
        // Get departments for assignment
        $departments = $this->getDepartments();

        // Create Presidential Priorities
        $priorities = $this->createPresidentialPriorities();

        // Create Sectoral Goals with department relationships
        $sectoralGoals = $this->createSectoralGoals($departments);

        // Create Bond Outcomes with department relationships
        $bondOutcomes = $this->createBondOutcomes($departments);

        // Create NLGAS Pillars with department relationships
        $pillars = $this->createNlgasPillars($departments);

        // Create Programs
        $programs = $this->createPrograms($pillars, $departments);

        // Create Indicators
        $this->createIndicators($programs, $sectoralGoals, $bondOutcomes, $pillars, $departments);

        // Create Cross-Cutting Metrics
        $this->createCrossCuttingMetrics($departments);

        // Create Strategic Alignments
        $this->createStrategicAlignments($priorities, $sectoralGoals, $bondOutcomes, $pillars);
    }

    private function getDepartments()
    {
        return [
            'production' => Department::where('slug', 'ruminants_monogastric_development')->first(),
            'veterinary' => Department::where('slug', 'animal_health_reproductive_services')->first(),
            'pastoral' => Department::where('slug', 'ranch_pastoral_resources_development')->first(),
            'marketing' => Department::where('slug', 'livestock_extension_business_development')->first(),
            'prs' => Department::where('slug', 'planning_research_statistics')->first(),
            'quality' => Department::where('slug', 'quality_assurance_certification')->first(),
            'vph' => Department::where('slug', 'veterinary_public_health_epidemiology')->first(),
            'tad' => Department::where('slug', 'transboundary_animal_disease')->first(),
        ];
    }

    private function createPresidentialPriorities()
    {
        $pp1 = PresidentialPriority::create([
            'code' => 'PP1',
            'title' => 'Boost Agriculture to Achieve Food Security',
            'description' => 'Strengthen agricultural productivity and ensure national food security with livestock as a major pillar',
            'baseline_year' => 2024,
            'target_year' => 2029,
            'source_document' => 'Presidential Priority Framework'
        ]);

        $pp2 = PresidentialPriority::create([
            'code' => 'PP2',
            'title' => 'Reform the Economy for Sustained and Inclusive Growth',
            'description' => 'Expand economic productivity, industrialize agriculture and create inclusive jobs and income opportunities',
            'baseline_year' => 2024,
            'target_year' => 2029,
            'source_document' => 'Presidential Priority Framework'
        ]);

        $pp3 = PresidentialPriority::create([
            'code' => 'PP3',
            'title' => 'Strengthen National Security for Peace and Prosperity',
            'description' => 'Enhance peace, security and stability especially addressing resource-related and communal conflicts',
            'baseline_year' => 2024,
            'target_year' => 2029,
            'source_document' => 'Presidential Priority Framework'
        ]);

        $pp4 = PresidentialPriority::create([
            'code' => 'PP4',
            'title' => 'Improve Governance for Effective Service Delivery',
            'description' => 'Institutionalize transparency, accountability and performance-based public management',
            'baseline_year' => 2024,
            'target_year' => 2029,
            'source_document' => 'Presidential Priority Framework'
        ]);

        return compact('pp1', 'pp2', 'pp3', 'pp4');
    }

    private function createSectoralGoals($departments)
    {
        $sg1 = SectoralGoal::create([
            'code' => 'SG1',
            'title' => 'Increased Livestock Production and Productivity',
            'description' => 'Herds/flocks expanded and per-animal yields raised to meet domestic food security and export targets',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Strategic Aspiration',
            'responsible_entity' => 'FMLD Planning Department',
            'department_id' => $departments['production']?->id
        ]);

        $sg2 = SectoralGoal::create([
            'code' => 'SG2',
            'title' => 'Enhanced Animal Health and Reduced Disease Burden',
            'description' => 'Disease prevalence and zoonotic spillover reduced through vaccination, surveillance and biosecurity systems',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Pillar 2; Bond BO1',
            'responsible_entity' => 'Department of Veterinary Services',
            'department_id' => $departments['veterinary']?->id
        ]);

        $sg3 = SectoralGoal::create([
            'code' => 'SG3',
            'title' => 'Improved Access to Livestock Inputs and Services',
            'description' => 'Feeds, veterinary drugs, AI services, and extension available to producers at scale',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Pillar 3; Bond BO1',
            'responsible_entity' => 'Department of Production & Extension',
            'department_id' => $departments['pastoral']?->id
        ]);

        $sg4 = SectoralGoal::create([
            'code' => 'SG4',
            'title' => 'Strengthened Market Access and Export Competitiveness',
            'description' => 'Modern markets, certification systems, and regional trade protocols unlock domestic and export demand',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Pillar 4; Bond BO3',
            'responsible_entity' => 'Department of Livestock Marketing',
            'department_id' => $departments['marketing']?->id
        ]);

        $sg5 = SectoralGoal::create([
            'code' => 'SG5',
            'title' => 'Enhanced Financial Inclusion and Private Investment',
            'description' => 'Credit, insurance, and PPP mechanisms mobilized to finance livestock transformation',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Pillar 5; Bond BO2',
            'responsible_entity' => 'Department of Livestock Marketing',
            'department_id' => $departments['marketing']?->id
        ]);

        $sg6 = SectoralGoal::create([
            'code' => 'SG6',
            'title' => 'Reduced Farmer-Herder Conflicts and Improved Social Cohesion',
            'description' => 'Grazing reserves, water points, and conflict resolution systems reduce violence and promote coexistence',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Pillar 6; Bond BO4',
            'responsible_entity' => 'Department of Pastoral Resources',
            'department_id' => $departments['pastoral']?->id
        ]);

        $sg7 = SectoralGoal::create([
            'code' => 'SG7',
            'title' => 'Inclusive Youth and Women Participation',
            'description' => 'Gender-responsive policies and youth employment pathways ensure equitable access to livestock opportunities',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Pillar 9; CCM2',
            'responsible_entity' => 'Department of Extension Services',
            'department_id' => $departments['marketing']?->id
        ]);

        $sg8 = SectoralGoal::create([
            'code' => 'SG8',
            'title' => 'Strengthened Data Systems and Evidence-Based Planning',
            'description' => 'Livestock statistics, traceability, and M&E systems enable informed decision-making',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Pillar 10; Bond BO5',
            'responsible_entity' => 'Department of Planning, Research & Statistics',
            'department_id' => $departments['prs']?->id
        ]);

        return compact('sg1', 'sg2', 'sg3', 'sg4', 'sg5', 'sg6', 'sg7', 'sg8');
    }

    private function createBondOutcomes($departments)
    {
        $bo1 = BondOutcome::create([
            'code' => 'BO1',
            'title' => 'Improved livestock productivity and enhanced health and extension services',
            'description' => 'Strengthen national livestock production through breed improvement, disease control and extension systems',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'FMLD Permanent Secretary',
            'department_id' => $departments['production']?->id
        ]);

        $bo2 = BondOutcome::create([
            'code' => 'BO2',
            'title' => 'Transformed livestock value chain infrastructure through PPP',
            'description' => 'Rehabilitate and expand livestock infrastructure via PPPs to drive modernization',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'FMLD PPP Unit',
            'department_id' => $departments['marketing']?->id
        ]);

        $bo3 = BondOutcome::create([
            'code' => 'BO3',
            'title' => 'Enhanced export orientation and market competitiveness',
            'description' => 'Strengthen certification systems and regional trade protocols for export markets',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'Department of Marketing',
            'department_id' => $departments['marketing']?->id
        ]);

        $bo4 = BondOutcome::create([
            'code' => 'BO4',
            'title' => 'Reduced incidences of farmer-herder conflicts',
            'description' => 'Establish grazing reserves and water points to mitigate resource conflicts',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'Department of Pastoral Resources',
            'department_id' => $departments['pastoral']?->id
        ]);

        $bo5 = BondOutcome::create([
            'code' => 'BO5',
            'title' => 'Enhanced livestock innovation and information system',
            'description' => 'Deploy NLIMS and traceability systems for data-driven planning',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'Department of PRS',
            'department_id' => $departments['prs']?->id
        ]);

        $bo6 = BondOutcome::create([
            'code' => 'BO6',
            'title' => 'Improved nutrition and food security',
            'description' => 'Increase animal protein availability and consumption nationwide',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'Department of Production',
            'department_id' => $departments['production']?->id
        ]);

        $bo7 = BondOutcome::create([
            'code' => 'BO7',
            'title' => 'Efficient service delivery and institutional strengthening',
            'description' => 'Reform FMLD processes and build staff capacity for performance excellence',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'Permanent Secretary',
            'department_id' => $departments['prs']?->id
        ]);

        $bo8 = BondOutcome::create([
            'code' => 'BO8',
            'title' => 'E-government and citizens engagement',
            'description' => 'Digitalize service delivery and enhance stakeholder communication',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'ICT Department',
            'department_id' => $departments['prs']?->id
        ]);

        return compact('bo1', 'bo2', 'bo3', 'bo4', 'bo5', 'bo6', 'bo7', 'bo8');
    }

    private function createNlgasPillars($departments)
    {
        $p1 = NlgasPillar::create([
            'code' => 'P1',
            'title' => 'Livestock Value Chain Development & Market Access',
            'description' => 'Production, processing & value addition systems',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Production',
            'department_id' => $departments['production']?->id
        ]);

        $p2 = NlgasPillar::create([
            'code' => 'P2',
            'title' => 'Animal Health & Zoonoses Control',
            'description' => 'Disease prevention, surveillance & control systems',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Veterinary Services',
            'department_id' => $departments['veterinary']?->id
        ]);

        $p3 = NlgasPillar::create([
            'code' => 'P3',
            'title' => 'Feed & Fodder Development',
            'description' => 'Nutrition security through feed production & distribution',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Pastoral Resources',
            'department_id' => $departments['pastoral']?->id
        ]);

        $p4 = NlgasPillar::create([
            'code' => 'P4',
            'title' => 'Water Resources Management',
            'description' => 'Water access for livestock & conflict prevention',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Pastoral Resources',
            'department_id' => $departments['pastoral']?->id
        ]);

        $p5 = NlgasPillar::create([
            'code' => 'P5',
            'title' => 'Finance & Insurance',
            'description' => 'Financial access & investment mobilization',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Marketing & Business Development',
            'department_id' => $departments['marketing']?->id
        ]);

        $p6 = NlgasPillar::create([
            'code' => 'P6',
            'title' => 'Peacebuilding, Security & Social Cohesion',
            'description' => 'Conflict management & pastoral livelihoods',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Pastoral Resources',
            'department_id' => $departments['pastoral']?->id
        ]);

        $p7 = NlgasPillar::create([
            'code' => 'P7',
            'title' => 'Infrastructure Development & Waste Management',
            'description' => 'Physical infrastructure & environmental sustainability',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Production',
            'department_id' => $departments['production']?->id
        ]);

        $p8 = NlgasPillar::create([
            'code' => 'P8',
            'title' => 'Livestock Extension Services',
            'description' => 'Knowledge transfer & capacity building',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Extension Services',
            'department_id' => $departments['marketing']?->id
        ]);

        $p9 = NlgasPillar::create([
            'code' => 'P9',
            'title' => 'Youth & Women Empowerment',
            'description' => 'Inclusive participation & employment generation',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Extension Services',
            'department_id' => $departments['marketing']?->id
        ]);

        $p10 = NlgasPillar::create([
            'code' => 'P10',
            'title' => 'Livestock Statistics & Information Systems',
            'description' => 'Data management, traceability & M&E',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Planning, Research & Statistics',
            'department_id' => $departments['prs']?->id
        ]);

        return compact('p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9', 'p10');
    }

    private function createPrograms($pillars, $departments)
    {
        // Sample programs - add more based on HTML document
        $programs = [];

        $programs[] = Program::create([
            'code' => 'PROG1.1',
            'title' => 'Breed Improvement & Genetic Enhancement',
            'description' => 'AI services, nucleus herds, and breeding protocols',
            'nlgas_pillar_id' => $pillars['p1']->id,
            'department_id' => $departments['production']?->id,
            'baseline_year' => 2024,
            'target_year' => 2027,
            'priority_level' => 'high',
            'source_document' => 'NLGAS Implementation Plan'
        ]);

        $programs[] = Program::create([
            'code' => 'PROG2.1',
            'title' => 'Vaccination & Disease Surveillance',
            'description' => 'National vaccination campaigns and epidemiological monitoring',
            'nlgas_pillar_id' => $pillars['p2']->id,
            'department_id' => $departments['veterinary']?->id,
            'baseline_year' => 2024,
            'target_year' => 2027,
            'priority_level' => 'high',
            'source_document' => 'NLGAS Implementation Plan'
        ]);

        return $programs;
    }

    private function createIndicators($programs, $sectoralGoals, $bondOutcomes, $pillars, $departments)
    {
        // Sample indicators from HTML document
        Indicator::create([
            'code' => 'IND1.1.1',
            'title' => 'National cattle herd size (million heads)',
            'description' => 'Total number of cattle in Nigeria',
            'program_id' => $programs[0]->id,
            'sectoral_goal_id' => $sectoralGoals['sg1']->id,
            'bond_outcome_id' => $bondOutcomes['bo1']->id,
            'nlgas_pillar_id' => $pillars['p1']->id,
            'department_id' => $departments['production']?->id,
            'indicator_type' => 'outcome',
            'measurement_unit' => 'million heads',
            'baseline_value' => 20.7,
            'baseline_year' => 2024,
            'target_value' => 25.0,
            'target_year' => 2027,
            'data_source' => 'NLIMS/PRS',
            'collection_frequency' => 'Annual',
            'responsible_entity' => 'Department of Production',
            'tier_level' => 1
        ]);

        Indicator::create([
            'code' => 'IND2.1.1',
            'title' => 'Vaccination coverage for PPR (% of small ruminants)',
            'description' => 'Percentage of goats and sheep vaccinated against Peste des Petits Ruminants',
            'program_id' => $programs[1]->id,
            'sectoral_goal_id' => $sectoralGoals['sg2']->id,
            'bond_outcome_id' => $bondOutcomes['bo1']->id,
            'nlgas_pillar_id' => $pillars['p2']->id,
            'department_id' => $departments['veterinary']?->id,
            'indicator_type' => 'output',
            'measurement_unit' => 'percentage',
            'baseline_value' => 35.0,
            'baseline_year' => 2024,
            'target_value' => 70.0,
            'target_year' => 2027,
            'data_source' => 'DVS Reports',
            'collection_frequency' => 'Quarterly',
            'responsible_entity' => 'Department of Veterinary Services',
            'tier_level' => 2
        ]);
    }

    private function createCrossCuttingMetrics($departments)
    {
        CrossCuttingMetric::create([
            'code' => 'CCM1',
            'title' => 'Women participation in livestock value chains (%)',
            'description' => 'Percentage of women actively engaged in livestock production and marketing',
            'department_id' => $departments['marketing']?->id,
            'measurement_unit' => 'percentage',
            'baseline_value' => 25.0,
            'baseline_year' => 2024,
            'target_value' => 40.0,
            'target_year' => 2027,
            'data_source' => 'NLIMS Gender Module',
            'collection_frequency' => 'Annual',
            'responsible_entity' => 'Department of Extension Services',
            'metric_category' => 'gender'
        ]);

        CrossCuttingMetric::create([
            'code' => 'CCM2',
            'title' => 'Youth employment in livestock sector (jobs created)',
            'description' => 'Number of youth employed in formal livestock enterprises',
            'department_id' => $departments['marketing']?->id,
            'measurement_unit' => 'number',
            'baseline_value' => 5000,
            'baseline_year' => 2024,
            'target_value' => 50000,
            'target_year' => 2027,
            'data_source' => 'NLIMS Youth Module',
            'collection_frequency' => 'Quarterly',
            'responsible_entity' => 'Department of Extension Services',
            'metric_category' => 'youth'
        ]);

        CrossCuttingMetric::create([
            'code' => 'CCM3',
            'title' => 'Climate-smart livestock practices adoption (%)',
            'description' => 'Percentage of farms adopting climate adaptation technologies',
            'department_id' => $departments['pastoral']?->id,
            'measurement_unit' => 'percentage',
            'baseline_value' => 10.0,
            'baseline_year' => 2024,
            'target_value' => 35.0,
            'target_year' => 2027,
            'data_source' => 'Extension Reports',
            'collection_frequency' => 'Annual',
            'responsible_entity' => 'Department of Pastoral Resources',
            'metric_category' => 'climate'
        ]);

        CrossCuttingMetric::create([
            'code' => 'CCM4',
            'title' => 'Digital innovation adoption in livestock sector (%)',
            'description' => 'Percentage of livestock enterprises using digital tools',
            'department_id' => $departments['prs']?->id,
            'measurement_unit' => 'percentage',
            'baseline_value' => 5.0,
            'baseline_year' => 2024,
            'target_value' => 25.0,
            'target_year' => 2027,
            'data_source' => 'NLIMS ICT Module',
            'collection_frequency' => 'Quarterly',
            'responsible_entity' => 'ICT Department',
            'metric_category' => 'innovation'
        ]);
    }

    private function createStrategicAlignments($priorities, $sectoralGoals, $bondOutcomes, $pillars)
    {
        // PP1 → SG1 → BO1 → P1
        StrategicAlignment::create([
            'presidential_priority_id' => $priorities['pp1']->id,
            'sectoral_goal_id' => $sectoralGoals['sg1']->id,
            'bond_outcome_id' => $bondOutcomes['bo1']->id,
            'nlgas_pillar_id' => $pillars['p1']->id,
            'alignment_notes' => 'Core production and productivity alignment'
        ]);

        // PP1 → SG2 → BO1 → P2
        StrategicAlignment::create([
            'presidential_priority_id' => $priorities['pp1']->id,
            'sectoral_goal_id' => $sectoralGoals['sg2']->id,
            'bond_outcome_id' => $bondOutcomes['bo1']->id,
            'nlgas_pillar_id' => $pillars['p2']->id,
            'alignment_notes' => 'Animal health and disease control alignment'
        ]);

        // PP2 → SG4 → BO3 → P1
        StrategicAlignment::create([
            'presidential_priority_id' => $priorities['pp2']->id,
            'sectoral_goal_id' => $sectoralGoals['sg4']->id,
            'bond_outcome_id' => $bondOutcomes['bo3']->id,
            'nlgas_pillar_id' => $pillars['p1']->id,
            'alignment_notes' => 'Market access and export competitiveness'
        ]);

        // PP3 → SG6 → BO4 → P6
        StrategicAlignment::create([
            'presidential_priority_id' => $priorities['pp3']->id,
            'sectoral_goal_id' => $sectoralGoals['sg6']->id,
            'bond_outcome_id' => $bondOutcomes['bo4']->id,
            'nlgas_pillar_id' => $pillars['p6']->id,
            'alignment_notes' => 'Conflict reduction and social cohesion'
        ]);

        // PP4 → SG8 → BO5 → P10
        StrategicAlignment::create([
            'presidential_priority_id' => $priorities['pp4']->id,
            'sectoral_goal_id' => $sectoralGoals['sg8']->id,
            'bond_outcome_id' => $bondOutcomes['bo5']->id,
            'nlgas_pillar_id' => $pillars['p10']->id,
            'alignment_notes' => 'Data systems and evidence-based planning'
        ]);
    }
}
