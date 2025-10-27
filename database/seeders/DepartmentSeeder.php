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
        $departments = [

            // COMMON SERVICES DEPARTMENTS
            [
                'name' => 'Human Resources Management',
                'slug' => 'human_resources_management',
                'is_technical' => false,
            ],
            [
                'name' => 'Finance and Accounts',
                'slug' => 'finance_and_accounts',
                'is_technical' => false,
            ],
            [
                'name' => 'Planning, Research and Statistics',
                'slug' => 'planning_research_statistics',
                'is_technical' => false,
            ],
            [
                'name' => 'General Services',
                'slug' => 'general_services',
                'is_technical' => false,
            ],
            [
                'name' => 'Procurement',
                'slug' => 'procurement',
                'is_technical' => false,
            ],
            [
                'name' => 'Reform Coordination',
                'slug' => 'reform_coordination',
                'is_technical' => false,
            ],

            // TECHNICAL DEPARTMENTS
            [
                'name' => 'Ruminants & Monogastric Development',
                'slug' => 'ruminants_monogastric_development',
                'is_technical' => true,
            ],
            [
                'name' => 'Animal Health & Reproductive Services',
                'slug' => 'animal_health_reproductive_services',
                'is_technical' => true,
            ],
            [
                'name' => 'Ranch & Pastoral Resources Development',
                'slug' => 'ranch_pastoral_resources_development',
                'is_technical' => true,
            ],
            [
                'name' => 'Pest Control Services',
                'slug' => 'pest_control_services',
                'is_technical' => true,
            ],
            [
                'name' => 'Quality Assurance & Certification',
                'slug' => 'quality_assurance_certification',
                'is_technical' => true,
            ],
            [
                'name' => 'Veterinary Public Health & Epidemiology',
                'slug' => 'veterinary_public_health_epidemiology',
                'is_technical' => true,
            ],
            [
                'name' => 'Livestock Extension & Business Development',
                'slug' => 'livestock_extension_business_development',
                'is_technical' => true,
            ],

            // MINISTERIAL UNITS
            [
                'name' => 'Legal Unit',
                'slug' => 'legal_unit',
                'is_technical' => false,
            ],
            [
                'name' => 'Internal Audit Unit',
                'slug' => 'internal_audit_unit',
                'is_technical' => false,
            ],
            [
                'name' => 'Press & Public Relations Unit',
                'slug' => 'press_public_relations_unit',
                'is_technical' => false,
            ],
            [
                'name' => 'Special Duties Unit',
                'slug' => 'special_duties_unit',
                'is_technical' => false,
            ],
        ];

        Department::insert($departments);
    }
}
