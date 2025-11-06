<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main department categories
        $units = Department::create([
            'name' => 'Units',
            'slug' => 'units',
            'is_technical' => false,
        ]);

        $specialDuties = Department::create([
            'name' => 'Special Duties',
            'slug' => 'special_duties',
            'is_technical' => false,
        ]);

        $commonServices = Department::create([
            'name' => 'Common Services Departments',
            'slug' => 'common_services_departments',
            'is_technical' => false,
        ]);

        $technical = Department::create([
            'name' => 'Technical Departments',
            'slug' => 'technical_departments',
            'is_technical' => true,
        ]);

        // Units Departments
        $unitsChildren = [
            ['name' => 'Legal', 'slug' => 'legal', 'is_technical' => false],
            ['name' => 'Internal Audit', 'slug' => 'internal_audit', 'is_technical' => false],
            ['name' => 'Press & Public Relations', 'slug' => 'press_public_relations', 'is_technical' => false],
        ];

        foreach ($unitsChildren as $child) {
            Department::create(array_merge($child, ['parent_id' => $units->id]));
        }

        // Special Duties Departments
        $specialDutiesChildren = [
            ['name' => 'Protocol', 'slug' => 'protocol', 'is_technical' => false],
            ['name' => 'ACTU', 'slug' => 'actu', 'is_technical' => false],
            ['name' => 'Stock Verification', 'slug' => 'stock_verification', 'is_technical' => false],
            ['name' => 'PPP', 'slug' => 'ppp', 'is_technical' => false],
            ['name' => 'Liaison/State Office Coordination', 'slug' => 'liaison_state_office_coordination', 'is_technical' => false],
        ];

        foreach ($specialDutiesChildren as $child) {
            Department::create(array_merge($child, ['parent_id' => $specialDuties->id]));
        }

        // Common Services Departments and their subdivisions
        $commonServicesData = [
            [
                'name' => 'Human Resources Management',
                'slug' => 'human_resources_management',
                'is_technical' => false,
                'children' => [
                    ['name' => 'Appointment, Promotion & Discipline', 'slug' => 'appointment_promotion_discipline'],
                    ['name' => 'Staff Welfare & Training', 'slug' => 'staff_welfare_training'],
                ]
            ],
            [
                'name' => 'Finance and Accounts',
                'slug' => 'finance_and_accounts',
                'is_technical' => false,
                'children' => [
                    ['name' => 'Expenditure', 'slug' => 'expenditure'],
                    ['name' => 'Budget', 'slug' => 'budget'],
                    ['name' => 'Fiscal & Final Reporting', 'slug' => 'fiscal_final_reporting'],
                ]
            ],
            [
                'name' => 'Planning, Research and Statistics',
                'slug' => 'planning_research_statistics',
                'is_technical' => false,
                'children' => [
                    ['name' => 'Planning', 'slug' => 'planning'],
                    ['name' => 'Research and Statistics', 'slug' => 'research_statistics'],
                    ['name' => 'Monitoring & Evaluation', 'slug' => 'monitoring_evaluation'],
                ]
            ],
            [
                'name' => 'General Services',
                'slug' => 'general_services',
                'is_technical' => false,
                'children' => [
                    ['name' => 'General Services', 'slug' => 'general_services_sub'],
                    ['name' => 'Recurrent', 'slug' => 'recurrent'],
                    ['name' => 'ICT', 'slug' => 'ict'],
                ]
            ],
            [
                'name' => 'Procurement',
                'slug' => 'procurement',
                'is_technical' => false,
                'children' => [
                    ['name' => 'Capital', 'slug' => 'capital'],
                    ['name' => 'Recurrent', 'slug' => 'procurement_recurrent'],
                ]
            ],
            [
                'name' => 'Reform Coordination',
                'slug' => 'reform_coordination',
                'is_technical' => false,
                'children' => [
                    ['name' => 'Reform Coordination', 'slug' => 'reform_coordination_sub'],
                    ['name' => 'SERVICOM', 'slug' => 'servicom'],
                    ['name' => 'Service Innovation', 'slug' => 'service_innovation'],
                ]
            ],
        ];

        foreach ($commonServicesData as $dept) {
            $parent = Department::create([
                'name' => $dept['name'],
                'slug' => $dept['slug'],
                'is_technical' => $dept['is_technical'],
                'parent_id' => $commonServices->id
            ]);

            foreach ($dept['children'] as $child) {
                Department::create(array_merge($child, [
                    'is_technical' => false,
                    'parent_id' => $parent->id
                ]));
            }
        }

        // Technical Departments and their subdivisions
        $technicalData = [
            [
                'name' => 'Ruminants & Monogastric Development',
                'slug' => 'ruminants_monogastric_development',
                'children' => [
                    ['name' => 'Dairy Production', 'slug' => 'dairy_production'],
                    ['name' => 'Beef Production', 'slug' => 'beef_production'],
                    ['name' => 'Sheep and Goat Production', 'slug' => 'sheep_goat_production'],
                    ['name' => 'Pig Production', 'slug' => 'pig_production'],
                    ['name' => 'Poultry & Micro Livestock', 'slug' => 'poultry_micro_livestock'],
                ]
            ],
            [
                'name' => 'Animal Health & Reproductive Services',
                'slug' => 'animal_health_reproductive_services',
                'children' => [
                    ['name' => 'Disease Prevention & Control', 'slug' => 'disease_prevention_control'],
                    ['name' => 'Monogastric Animal Disease', 'slug' => 'monogastric_animal_disease'],
                    ['name' => 'Vet Clinical Services & Sanitary', 'slug' => 'vet_clinical_services_sanitary'],
                    ['name' => 'Reproductive Health Mgt, Artificial Insemination & Embryo Transfer', 'slug' => 'reproductive_health_mgt'],
                    ['name' => 'Aquatic Health', 'slug' => 'aquatic_health'],
                ]
            ],
            [
                'name' => 'Ranch & Pastoral Resources Development',
                'slug' => 'ranch_pastoral_resources_development',
                'children' => [
                    ['name' => 'Grazing Reserves & Stock Route Development', 'slug' => 'grazing_reserves'],
                    ['name' => 'Ranch Development', 'slug' => 'ranch_development'],
                    ['name' => 'Feed & Fodder', 'slug' => 'feed_fodder'],
                    ['name' => 'Strategic Food Reserve', 'slug' => 'strategic_food_reserve'],
                ]
            ],
            [
                'name' => 'Pest Control Services',
                'slug' => 'pest_control_services',
                'children' => [
                    ['name' => 'Pest and Vector Control', 'slug' => 'pest_vector_control'],
                    ['name' => 'Integrated Pest Management', 'slug' => 'integrated_pest_management'],
                    ['name' => 'Pesticides Quality Assurance and Lab Services', 'slug' => 'pesticides_quality'],
                ]
            ],
            [
                'name' => 'Quality Assurance & Certification',
                'slug' => 'quality_assurance_certification',
                'children' => [
                    ['name' => 'Certification', 'slug' => 'certification'],
                    ['name' => 'Veterinary Regulation & Inspectorate', 'slug' => 'veterinary_regulation'],
                    ['name' => 'Veterinary Drug & Feed Safety', 'slug' => 'veterinary_drug_feed_safety'],
                ]
            ],
            [
                'name' => 'Veterinary Public Health & Epidemiology',
                'slug' => 'veterinary_public_health_epidemiology',
                'children' => [
                    ['name' => 'Control & Health & Antimicrobial Resistance', 'slug' => 'control_health_antimicrobial'],
                    ['name' => 'Veterinary Epidemiology', 'slug' => 'veterinary_epidemiology'],
                    ['name' => 'Slaughterhouse & Food Safety Hygiene', 'slug' => 'slaughterhouse_food_safety'],
                    ['name' => 'Animal Identification & Traceability', 'slug' => 'animal_identification'],
                ]
            ],
            [
                'name' => 'Livestock Extension & Business Development',
                'slug' => 'livestock_extension_business_development',
                'children' => [
                    ['name' => 'Extension Services', 'slug' => 'extension_services'],
                    ['name' => 'Business Development & Entrepreneurship', 'slug' => 'business_development'],
                    ['name' => 'Gender & Youth Empowerment', 'slug' => 'gender_youth_empowerment'],
                    ['name' => 'Livestock Cooperative Development', 'slug' => 'livestock_cooperative'],
                ]
            ],
            [
                'name' => 'Transboundary Animal Disease',
                'slug' => 'transboundary_animal_disease',
                'children' => [
                    ['name' => 'Zoonotic Disease', 'slug' => 'zoonotic_disease'],
                    ['name' => 'Cross Border Collaboration', 'slug' => 'cross_border_collaboration'],
                    ['name' => 'Animal Welfare', 'slug' => 'animal_welfare'],
                    ['name' => 'Wildlife Disease Control & Animal Disease Risk Analysis', 'slug' => 'wildlife_disease_control'],
                    ['name' => 'Disease Management & Emergency Preparedness & Response', 'slug' => 'disease_management'],
                    ['name' => 'Bee Health', 'slug' => 'bee_health'],
                ]
            ],
        ];

        foreach ($technicalData as $dept) {
            $parent = Department::create([
                'name' => $dept['name'],
                'slug' => $dept['slug'],
                'is_technical' => true,
                'parent_id' => $technical->id
            ]);

            foreach ($dept['children'] as $child) {
                Department::create(array_merge($child, [
                    'is_technical' => true,
                    'parent_id' => $parent->id
                ]));
            }
        }
    }
}
