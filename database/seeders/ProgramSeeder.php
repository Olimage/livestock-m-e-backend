<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\NlgasPillar;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Helper function to get pillar ID by code
        $pillar = fn(string $code) => NlgasPillar::where('code', $code)->first()?->id;

        // Pillar 1 Programs: Economic Growth & Diversification
        Program::firstOrCreate(['code' => 'P1.1'], [
            'nlgas_pillar_id' => $pillar('P1'),
            'title' => 'Dairy',
        ]);

        Program::firstOrCreate(['code' => 'P1.2'], [
            'nlgas_pillar_id' => $pillar('P1'),
            'title' => 'Poultry',
        ]);

        Program::firstOrCreate(['code' => 'P1.3'], [
            'nlgas_pillar_id' => $pillar('P1'),
            'title' => 'Small Ruminant',
        ]);

        Program::firstOrCreate(['code' => 'P1.4'], [
            'nlgas_pillar_id' => $pillar('P1'),
            'title' => 'Beef',
        ]);

        Program::firstOrCreate(['code' => 'P1.5'], [
            'nlgas_pillar_id' => $pillar('P1'),
            'title' => 'Pig',
        ]);

        Program::firstOrCreate(['code' => 'P1.6'], [
            'nlgas_pillar_id' => $pillar('P1'),
            'title' => 'Leather',
        ]);

        Program::firstOrCreate(['code' => 'P1.7'], [
            'nlgas_pillar_id' => $pillar('P1'),
            'title' => 'Micro-Livestock',
        ]);

        // Pillar 2 Programs: Animal Health & Disease Control
        Program::firstOrCreate(['code' => 'P2.1'], [
            'nlgas_pillar_id' => $pillar('P2'),
            'title' => 'Health Supplies',
        ]);

        Program::firstOrCreate(['code' => 'P2.2'], [
            'nlgas_pillar_id' => $pillar('P2'),
            'title' => 'Surveillance',
        ]);

        Program::firstOrCreate(['code' => 'P2.3'], [
            'nlgas_pillar_id' => $pillar('P2'),
            'title' => 'TADs Control',
        ]);

        Program::firstOrCreate(['code' => 'P2.4'], [
            'nlgas_pillar_id' => $pillar('P2'),
            'title' => 'Diagnostics',
        ]);

        Program::firstOrCreate(['code' => 'P2.5'], [
            'nlgas_pillar_id' => $pillar('P2'),
            'title' => 'Trade Standards',
        ]);

        Program::firstOrCreate(['code' => 'P2.9'], [
            'nlgas_pillar_id' => $pillar('P2'),
            'title' => 'One Health',
        ]);

        // Pillar 3 Programs: Feed & Nutrition
        Program::firstOrCreate(['code' => 'P3.1'], [
            'nlgas_pillar_id' => $pillar('P3'),
            'title' => 'Feed Production & Processing Infrastructure',
        ]);

        Program::firstOrCreate(['code' => 'P3.2'], [
            'nlgas_pillar_id' => $pillar('P3'),
            'title' => 'Pasture Development & Management',
        ]);

        // Pillar 4 Programs: Water Resource Development
        Program::firstOrCreate(['code' => 'P4.1'], [
            'nlgas_pillar_id' => $pillar('P4'),
            'title' => 'Water Access',
        ]);

        Program::firstOrCreate(['code' => 'P4.2'], [
            'nlgas_pillar_id' => $pillar('P4'),
            'title' => 'Sustainable Management',
        ]);

        Program::firstOrCreate(['code' => 'P4.3'], [
            'nlgas_pillar_id' => $pillar('P4'),
            'title' => 'Conflict Reduction',
        ]);

        Program::firstOrCreate(['code' => 'P4.4'], [
            'nlgas_pillar_id' => $pillar('P4'),
            'title' => 'Climate Resilience',
        ]);

        // Pillar 5 Programs: Investment & Finance
        Program::firstOrCreate(['code' => 'P5.1'], [
            'nlgas_pillar_id' => $pillar('P5'),
            'title' => 'Livestock Credit & Guarantee Scheme',
        ]);

        Program::firstOrCreate(['code' => 'P5.2'], [
            'nlgas_pillar_id' => $pillar('P5'),
            'title' => 'Livestock Insurance Scheme',
        ]);

        // Pillar 6 Programs: Conflict Resolution
        Program::firstOrCreate(['code' => 'P6.1'], [
            'nlgas_pillar_id' => $pillar('P6'),
            'title' => 'Farmer-Herder Dialogue & Mediation',
        ]);

        Program::firstOrCreate(['code' => 'P6.2'], [
            'nlgas_pillar_id' => $pillar('P6'),
            'title' => 'Transhumance Route Mapping & Gazettement',
        ]);

        // Pillar 7 Programs: Infrastructure Development
        Program::firstOrCreate(['code' => 'P7.1'], [
            'nlgas_pillar_id' => $pillar('P7'),
            'title' => 'Modern Abattoir Construction',
        ]);

        Program::firstOrCreate(['code' => 'P7.2'], [
            'nlgas_pillar_id' => $pillar('P7'),
            'title' => 'Cold Chain & Storage Infrastructure',
        ]);

        // Pillar 8 Programs: Extension Services
        Program::firstOrCreate(['code' => 'P8.1'], [
            'nlgas_pillar_id' => $pillar('P8'),
            'title' => 'Extension Worker Training & Deployment',
        ]);

        Program::firstOrCreate(['code' => 'P8.2'], [
            'nlgas_pillar_id' => $pillar('P8'),
            'title' => 'Farmer Training & Demonstration Centers',
        ]);

        // Pillar 9 Programs: Gender & Youth Empowerment
        Program::firstOrCreate(['code' => 'P9.1'], [
            'nlgas_pillar_id' => $pillar('P9'),
            'title' => 'Women in Livestock Entrepreneurship',
        ]);

        Program::firstOrCreate(['code' => 'P9.2'], [
            'nlgas_pillar_id' => $pillar('P9'),
            'title' => 'Youth Livestock Skills & Employment',
        ]);

        // Pillar 10 Programs: Data & M&E
        Program::firstOrCreate(['code' => 'P10.1'], [
            'nlgas_pillar_id' => $pillar('P10'),
            'title' => 'National Livestock Information Management System (NLIMS)',
        ]);

        Program::firstOrCreate(['code' => 'P10.2'], [
            'nlgas_pillar_id' => $pillar('P10'),
            'title' => 'National Animal Identification & Traceability System (NAITS)',
        ]);

        $this->command->info('✓ Pillar Programs seeded successfully!');
    }
}
