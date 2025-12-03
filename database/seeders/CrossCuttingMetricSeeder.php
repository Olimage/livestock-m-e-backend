<?php

namespace Database\Seeders;

use App\Models\CrossCuttingMetric;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CrossCuttingMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metrics = [
            [
                'code' => 'CCM-1',
                'area' => 'Investment Tracking & Disbursement',
                'key_metric' => 'Total Investment, Federal Budget, Donor Funding, Private Investment, State Co-investment',
                'purpose' => 'Track financial flows across all programs'
            ],
            [
                'code' => 'CCM-2',
                'area' => 'Governance & Coordination',
                'key_metric' => 'JWG Establishment, Meetings, State JWGs, Recommendations, Inter-ministerial Coordination',
                'purpose' => 'Monitor institutional coordination effectiveness'
            ],
            [
                'code' => 'CCM-3',
                'area' => 'Environmental & Sustainability',
                'key_metric' => 'GHG Emissions, Sustainable Reserves, Soil Conservation, Breed Diversity',
                'purpose' => 'Ensure environmental sustainability'
            ],
            [
                'code' => 'CCM-4',
                'area' => 'Equity & Social Inclusion',
                'key_metric' => 'Women %, Youth %, Pastoralists %, Smallholders %, Gini Coefficient',
                'purpose' => 'Monitor inclusive participation and equity'
            ],
          
        ];

        foreach ($metrics as $metric) {
            CrossCuttingMetric::firstOrCreate(
                ['code' => $metric['code']],
                $metric
            );
        }
    }
}
