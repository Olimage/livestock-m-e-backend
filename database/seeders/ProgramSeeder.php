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
            'title' => 'Pasture Seeds',
        ]);

        Program::firstOrCreate(['code' => 'P3.2'], [
            'nlgas_pillar_id' => $pillar('P3'),
            'title' => 'Land Allocation',
        ]);

        Program::firstOrCreate(['code' => 'P3.3'], [
            'nlgas_pillar_id' => $pillar('P3'),
            'title' => 'Feed Budgeting',
        ]);

        Program::firstOrCreate(['code' => 'P3.4'], [
            'nlgas_pillar_id' => $pillar('P3'),
            'title' => 'Year-Round Production',
        ]);

        Program::firstOrCreate(['code' => 'P3.6'], [
            'nlgas_pillar_id' => $pillar('P3'),
            'title' => 'Grazing Reserves',
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
            'title' => 'Product Development',
        ]);

        Program::firstOrCreate(['code' => 'P5.5'], [
            'nlgas_pillar_id' => $pillar('P5'),
            'title' => 'Credit Scheme',
        ]);

        Program::firstOrCreate(['code' => 'P5.6'], [
            'nlgas_pillar_id' => $pillar('P5'),
            'title' => 'Insurance',
        ]);

        Program::firstOrCreate(['code' => 'P5.3'], [
            'nlgas_pillar_id' => $pillar('P5'),
            'title' => 'Market Access',
        ]);

        Program::firstOrCreate(['code' => 'P5.12'], [
            'nlgas_pillar_id' => $pillar('P5'),
            'title' => 'PPP Coordination',
        ]);

        // Pillar 6 Programs: Conflict Resolution
        Program::firstOrCreate(['code' => 'P6.1'], [
            'nlgas_pillar_id' => $pillar('P6'),
            'title' => 'Institutional Strengthening',
        ]);

        Program::firstOrCreate(['code' => 'P6.3'], [
            'nlgas_pillar_id' => $pillar('P6'),
            'title' => 'Capacity Building',
        ]);

        Program::firstOrCreate(['code' => 'P6.5'], [
            'nlgas_pillar_id' => $pillar('P6'),
            'title' => 'Transhumance Coordination',
        ]);

        Program::firstOrCreate(['code' => 'P6.6'], [
            'nlgas_pillar_id' => $pillar('P6'),
            'title' => 'Rangers Deployment',
        ]);

        // Pillar 7 Programs: Infrastructure Development
        Program::firstOrCreate(['code' => 'P7.1'], [
            'nlgas_pillar_id' => $pillar('P7'),
            'title' => 'Production Facilities',
        ]);

        Program::firstOrCreate(['code' => 'P7.2'], [
            'nlgas_pillar_id' => $pillar('P7'),
            'title' => 'Health Infrastructure',
        ]);

        Program::firstOrCreate(['code' => 'P7.6'], [
            'nlgas_pillar_id' => $pillar('P7'),
            'title' => 'Processing',
        ]);

        Program::firstOrCreate(['code' => 'P7.5'], [
            'nlgas_pillar_id' => $pillar('P7'),
            'title' => 'Transport',
        ]);

        Program::firstOrCreate(['code' => 'P7.9'], [
            'nlgas_pillar_id' => $pillar('P7'),
            'title' => 'Waste Management',
        ]);

        // Pillar 8 Programs: Extension Services
        Program::firstOrCreate(['code' => 'P8.1'], [
            'nlgas_pillar_id' => $pillar('P8'),
            'title' => 'National Policy',
        ]);

        Program::firstOrCreate(['code' => 'P8.2'], [
            'nlgas_pillar_id' => $pillar('P8'),
            'title' => 'LEW Formalization',
        ]);

        Program::firstOrCreate(['code' => 'P8.3'], [
            'nlgas_pillar_id' => $pillar('P8'),
            'title' => 'Training Modules',
        ]);

        Program::firstOrCreate(['code' => 'P8.4'], [
            'nlgas_pillar_id' => $pillar('P8'),
            'title' => 'LEW Recruitment',
        ]);

        Program::firstOrCreate(['code' => 'P8.5'], [
            'nlgas_pillar_id' => $pillar('P8'),
            'title' => 'Service Delivery',
        ]);

        // Pillar 9 Programs: Gender & Youth Empowerment
        Program::firstOrCreate(['code' => 'P9.1'], [
            'nlgas_pillar_id' => $pillar('P9'),
            'title' => 'Cooperatives',
        ]);

        Program::firstOrCreate(['code' => 'P9.2'], [
            'nlgas_pillar_id' => $pillar('P9'),
            'title' => 'Gender Mainstreaming',
        ]);

        Program::firstOrCreate(['code' => 'P9.3'], [
            'nlgas_pillar_id' => $pillar('P9'),
            'title' => 'Credit Incentives',
        ]);

        Program::firstOrCreate(['code' => 'P9.4'], [
            'nlgas_pillar_id' => $pillar('P9'),
            'title' => 'Cottage Industries',
        ]);

        Program::firstOrCreate(['code' => 'P9.5'], [
            'nlgas_pillar_id' => $pillar('P9'),
            'title' => 'Agribusiness Training',
        ]);

        // Pillar 10 Programs: Data & M&E
        Program::firstOrCreate(['code' => 'P10.1'], [
            'nlgas_pillar_id' => $pillar('P10'),
            'title' => 'Census & Survey',
        ]);

        Program::firstOrCreate(['code' => 'P10.2'], [
            'nlgas_pillar_id' => $pillar('P10'),
            'title' => 'Traceability/NAITS',
        ]);

        Program::firstOrCreate(['code' => 'P10.3'], [
            'nlgas_pillar_id' => $pillar('P10'),
            'title' => 'NLIMS',
        ]);

        Program::firstOrCreate(['code' => 'P10.4'], [
            'nlgas_pillar_id' => $pillar('P10'),
            'title' => 'Capacity Building',
        ]);

        $this->command->info('✓ Pillar Programs seeded successfully!');
    }
}
