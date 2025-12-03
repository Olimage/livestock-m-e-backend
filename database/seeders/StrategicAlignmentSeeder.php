<?php
namespace Database\Seeders;

use App\Models\BondOutcome;
use App\Models\NlgasPillar;
use App\Models\PresidentialPriority;
use App\Models\SectoralGoal;
use App\Models\Department;
use Illuminate\Database\Seeder;

class StrategicAlignmentSeeder extends Seeder
{
    public function run()
    {
        // Helper to fetch department IDs by slug (fallback null if missing)
        $dept = fn(string $slug) => Department::where('slug', $slug)->value('id');

        // Presidential Priorities (idempotent)
        $pp1 = PresidentialPriority::firstOrCreate([
            'code' => 'PP1',
        ],[
            'title' => 'Boost Agriculture to Achieve Food Security',
            'description' => 'Strengthen agricultural productivity and ensure national food security with livestock as a major pillar',
      
        ]);

        $pp2 = PresidentialPriority::firstOrCreate([
            'code' => 'PP2'
        ],[
            'title' => 'Reform the Economy for Sustained and Inclusive Growth',
            'description' => 'Expand economic productivity industrialize agriculture and create inclusive jobs and income opportunities',

        ]);

        $pp3 = PresidentialPriority::firstOrCreate([
            'code' => 'PP3'
        ],[
            'title' => 'Strengthen National Security for Peace and Prosperity',
            'description' => 'Enhance peace security and stability especially addressing resource-related and communal conflicts',

        ]);

        $pp4 = PresidentialPriority::firstOrCreate([
            'code' => 'PP4'
        ],[
            'title' => 'Improve Governance for Effective Service Delivery',
            'description' => 'Institutionalize transparency accountability and performance-based public management',

        ]);

        // Sectoral Goals
        $sg1 = SectoralGoal::firstOrCreate([
            'code' => 'SG1',
        ],[
            'title' => 'Increased Livestock Production and Productivity',
            'description' => 'Herds/flocks expanded and per-animal yields raised to meet domestic food security and export targets',

        ]);

        $sg2 = SectoralGoal::firstOrCreate([
            'code' => 'SG2',
        ],[
            'title' => 'Enhanced Animal Health and Reduced Disease Burden',
            'description' => 'Disease prevalence and zoonotic spillover reduced through vaccination surveillance and biosecurity systems',

        ]);
        $sg3 = SectoralGoal::firstOrCreate([
            'code' => 'SG3',
        ],[
            'title' => 'Improved Access to Livestock Inputs and Services',
            'description' => 'Improved Access to Livestock Inputs and Services',

        ]);
        $sg4 = SectoralGoal::firstOrCreate([
            'code' => 'SG4',
        ],[
            'title' => 'Strengthened Market Access and Export Competitiveness',
            'description' => 'Strengthened Market Access and Export Competitiveness',

        ]);
        $sg5 = SectoralGoal::firstOrCreate([
            'code' => 'SG5',
        ],[
            'title' => 'Enhanced Financial Inclusion and Private Investment',
            'description' => 'Enhanced Financial Inclusion and Private Investment',

        ]);
        $sg6 = SectoralGoal::firstOrCreate([
            'code' => 'SG6',
        ],[
            'title' => 'Reduced Farmer-Herder Conflicts and Social Cohesion',
            'description' => 'Reduced Farmer-Herder Conflicts and Social Cohesion',

        ]);
        $sg7 = SectoralGoal::firstOrCreate([
            'code' => 'SG7',
        ],[
            'title' => 'Inclusive Youth and Women Participation',
            'description' => 'Inclusive Youth and Women Participation',

        ]);
        $sg8 = SectoralGoal::firstOrCreate([
            'code' => 'SG8',
        ],[
            'title' => 'Strengthened Data Systems and Evidence-Based Planning',
            'description' => 'Strengthened Data Systems and Evidence-Based Planning',

        ]);


        // Bond Outcomes (idempotent + department mapping)
        $bo1 = BondOutcome::firstOrCreate([
            'code' => 'BO1'
        ], [
            'title' => 'Improved livestock productivity and enhanced health and extension services',
            'description' => 'Strengthen national livestock production through breed improvement disease control and extension systems that boost productivity and resilience',

        ]);

        $bo2 = BondOutcome::firstOrCreate([
            'code' => 'BO2'
        ], [
            'title' => 'Transformed livestock value chain infrastructure through PPP',
            'description' => 'Rehabilitate and expand livestock infrastructure via PPPs to drive modernization and competitiveness',

        ]);
        $bo3 = BondOutcome::firstOrCreate([
            'code' => 'BO3'
        ], [
            'title' => 'Enhanced export orientation',
            'description' => 'Enhanced export orientation',

        ]);
        $bo4 = BondOutcome::firstOrCreate([
            'code' => 'BO4'
        ], [
            'title' => 'Reduced incidences of conflicts',
            'description' => 'Reduced incidences of conflicts',

        ]);
        $bo5 = BondOutcome::firstOrCreate([
            'code' => 'BO5'
        ], [
            'title' => 'Enhanced livestock innovation and information system',
            'description' => 'Enhanced livestock innovation and information system',

        ]);
        $bo6 = BondOutcome::firstOrCreate([
            'code' => 'BO6'
        ], [
            'title' => 'Improved nutrition',
            'description' => 'Improved nutrition',

        ]);
        $bo7 = BondOutcome::firstOrCreate([
            'code' => 'BO7'
        ], [
            'title' => 'Efficient service delivery',
            'description' => 'Efficient service delivery',

        ]);
        $bo8 = BondOutcome::firstOrCreate([
            'code' => 'BO8'
        ], [
            'title' => 'E-government and citizens engagement',
            'description' => 'E-government and citizens engagement',

        ]);


        // NLGAS Pillars (idempotent + department mapping)
        $p1 = NlgasPillar::firstOrCreate([
            'code' => 'P1'
        ], [
            'title' => 'Livestock Value Chain Development & Market Access',
            'description' => 'Production & Value Addition',

        ]);

        $p2 = NlgasPillar::firstOrCreate([
            'code' => 'P2'
        ], [
            'title' => 'Animal Health & Zoonoses Control',
            'description' => 'Disease Prevention & Control',

        ]);
        $p3 = NlgasPillar::firstOrCreate([
            'code' => 'P3'
        ], [
            'title' => 'Feed & Fodder Development',
            'description' => 'Nutrition Security',

        ]);

        $p4 = NlgasPillar::firstOrCreate([
            'code' => 'P4'
        ], [
            'title' => 'Water Resources Management',
            'description' => 'Water Access & Conflict Prevention',

        ]);
        $p5 = NlgasPillar::firstOrCreate([
            'code' => 'P5'
        ], [
            'title' => 'Finance & Insurance',
            'description' => 'Financial Access & Investment',

        ]);

        $p6 = NlgasPillar::firstOrCreate([
            'code' => 'P6'
        ], [
            'title' => 'Peacebuilding, Security & Social Cohesion',
            'description' => 'Conflict Management',

        ]);
        $p7 = NlgasPillar::firstOrCreate([
            'code' => 'P7'
        ], [
            'title' => 'Infrastructure Development & Waste Management',
            'description' => 'Physical Infrastructure',

        ]);

        $p8 = NlgasPillar::firstOrCreate([
            'code' => 'P8'
        ], [
            'title' => 'Livestock Extension Services',
            'description' => 'Knowledge Transfer',

        ]);
        $p9 = NlgasPillar::firstOrCreate([
            'code' => 'P9'
        ], [
            'title' => 'Youth & Women Empowerment',
            'description' => 'Inclusive Participation',

        ]);

        $p10 = NlgasPillar::firstOrCreate([
            'code' => 'P10'
        ], [
            'title' => 'Livestock Statistics & Information Systems',
            'description' => 'Data Management & Traceability',

        ]);


    }
}
