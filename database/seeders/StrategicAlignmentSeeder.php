<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresidentialPriority;
use App\Models\SectoralGoal;
use App\Models\BondOutcome;
use App\Models\NlgasPillar;
use App\Models\StrategicAlignment;

class StrategicAlignmentSeeder extends Seeder
{
    public function run()
    {
        // Presidential Priorities
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
            'description' => 'Expand economic productivity industrialize agriculture and create inclusive jobs and income opportunities',
            'baseline_year' => 2024,
            'target_year' => 2029,
            'source_document' => 'Presidential Priority Framework'
        ]);

        $pp3 = PresidentialPriority::create([
            'code' => 'PP3',
            'title' => 'Strengthen National Security for Peace and Prosperity',
            'description' => 'Enhance peace security and stability especially addressing resource-related and communal conflicts',
            'baseline_year' => 2024,
            'target_year' => 2029,
            'source_document' => 'Presidential Priority Framework'
        ]);

        $pp4 = PresidentialPriority::create([
            'code' => 'PP4',
            'title' => 'Improve Governance for Effective Service Delivery',
            'description' => 'Institutionalize transparency accountability and performance-based public management',
            'baseline_year' => 2024,
            'target_year' => 2029,
            'source_document' => 'Presidential Priority Framework'
        ]);

        // Sectoral Goals
        $sg1 = SectoralGoal::create([
            'code' => 'SG1',
            'title' => 'Increased Livestock Production and Productivity',
            'description' => 'Herds/flocks expanded and per-animal yields raised to meet domestic food security and export targets',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Strategic Aspiration',
            'responsible_entity' => 'FMLD Planning Department'
        ]);

        $sg2 = SectoralGoal::create([
            'code' => 'SG2',
            'title' => 'Enhanced Animal Health and Reduced Disease Burden',
            'description' => 'Disease prevalence and zoonotic spillover reduced through vaccination surveillance and biosecurity systems',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS Pillar 2; Bond BO1',
            'responsible_entity' => 'Department of Veterinary Services'
        ]);

        // Add more sectoral goals as needed...

        // Bond Outcomes
        $bo1 = BondOutcome::create([
            'code' => 'BO1',
            'title' => 'Improved livestock productivity and enhanced health and extension services',
            'description' => 'Strengthen national livestock production through breed improvement disease control and extension systems that boost productivity and resilience',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'FMLD Permanent Secretary'
        ]);

        $bo2 = BondOutcome::create([
            'code' => 'BO2',
            'title' => 'Transformed livestock value chain infrastructure through PPP',
            'description' => 'Rehabilitate and expand livestock infrastructure via PPPs to drive modernization and competitiveness',
            'baseline_year' => 2024,
            'target_year' => 2027,
            'source_document' => 'Ministerial Performance Bond',
            'responsible_entity' => 'FMLD PPP Unit'
        ]);

        // Add more bond outcomes as needed...

        // NLGAS Pillars
        $p1 = NlgasPillar::create([
            'code' => 'P1',
            'title' => 'Livestock Value Chain Development & Market Access',
            'description' => 'Modernize animal value chains boost productivity improve breeds and increase processing',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Production'
        ]);

        $p2 = NlgasPillar::create([
            'code' => 'P2',
            'title' => 'Animal Health & Zoonoses Control',
            'description' => 'Reduce disease burden and strengthen One Health resilience',
            'baseline_year' => 2024,
            'target_year' => 2035,
            'source_document' => 'NLGAS',
            'responsible_entity' => 'Department of Veterinary Services'
        ]);

        // Add more pillars as needed...

        // Create strategic alignments (example relationships)
        StrategicAlignment::create([
            'presidential_priority_id' => $pp1->id,
            'sectoral_goal_id' => $sg1->id,
            'bond_outcome_id' => $bo1->id,
            'nlgas_pillar_id' => $p1->id,
            'alignment_notes' => 'Core production alignment'
        ]);

        StrategicAlignment::create([
            'presidential_priority_id' => $pp1->id,
            'sectoral_goal_id' => $sg2->id,
            'bond_outcome_id' => $bo1->id,
            'nlgas_pillar_id' => $p2->id,
            'alignment_notes' => 'Health and disease control alignment'
        ]);

        // Add more strategic alignments based on your Sheet 3 data...
    }
}