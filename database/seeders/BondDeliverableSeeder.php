<?php

namespace Database\Seeders;

use App\Models\BondDeliverable;
use App\Models\Indicator;
use Illuminate\Database\Seeder;

class BondDeliverableSeeder extends Seeder
{
    private array $deliverables = [
        [
            'code'        => 'BD-1',
            'deliverable' => 'Improve indigenous breeds for increased livestock productivity',
            'indicators'  => ['BOI-1', 'BOI-2', 'BOI-3', 'BOI-4'],
        ],
        [
            'code'        => 'BD-2',
            'deliverable' => 'Initiatives to reduce prevalence of transboundary animal diseases and pests & zoonoses (diseases of public health importance)',
            'indicators'  => ['BOI-5', 'BOI-6', 'BOI-7', 'BOI-8', 'BOI-9'],
        ],
        [
            'code'        => 'BD-3',
            'deliverable' => 'Initiatives to increase access to feed (including fodder, pasture, conserved forages - hay, silage - etc) and water through cost-efficient business models',
            'indicators'  => ['BOI-10', 'BOI-11', 'BOI-12', 'BOI-13', 'BOI-14'],
        ],
        [
            'code'        => 'BD-4',
            'deliverable' => 'Strengthen Livestock Extension Service Delivery',
            'indicators'  => ['BOI-15'],
        ],
        [
            'code'        => 'BD-5',
            'deliverable' => 'Establish Animal Health/Livestock Emergency Operations Center for response to disease outbreak and natural disaster including flooding',
            'indicators'  => ['BOI-16'],
        ],
        [
            'code'        => 'BD-6',
            'deliverable' => 'Strengthen surveillance, monitoring and control of transboundary pest (Quelea birds) and vector (tse-tse fly and tick)',
            'indicators'  => ['BOI-17', 'BOI-18'],
        ],
        [
            'code'        => 'BD-7',
            'deliverable' => 'Initiatives to increase private investments into livestock and animal health sectors',
            'indicators'  => ['BOI-19', 'BOI-20'],
        ],
        [
            'code'        => 'BD-8',
            'deliverable' => 'Modernize livestock and veterinary infrastructure in each geopolitical zone to catalyze production and processing activities',
            'indicators'  => ['BOI-21', 'BOI-22', 'BOI-23', 'BOI-24', 'BOI-25', 'BOI-26'],
        ],
        [
            'code'        => 'BD-9',
            'deliverable' => 'Initiatives to increase export of livestock products',
            'indicators'  => ['BOI-27'],
        ],
        [
            'code'        => 'BD-10',
            'deliverable' => 'Develop livestock baseline and database',
            'indicators'  => ['BOI-28', 'BOI-29', 'BOI-30'],
        ],
        [
            'code'        => 'BD-11',
            'deliverable' => 'Improve local and international trade of livestock commodities',
            'indicators'  => ['BOI-31', 'BOI-32'],
        ],
        [
            'code'        => 'BD-12',
            'deliverable' => 'Develop a National Livestock Growth Acceleration Strategy (NLGAS)',
            'indicators'  => ['BOI-33'],
        ],
        [
            'code'        => 'BD-13',
            'deliverable' => 'Initiate and implement quarterly citizens and stakeholders engagement sessions to communicate government activities and serve as feedback mechanism',
            'indicators'  => ['BOI-34', 'BOI-35', 'BOI-36'],
        ],
        [
            'code'        => 'BD-14',
            'deliverable' => 'Support institutions to increase access to quality livestock products for improved nutrition',
            'indicators'  => ['BOI-37'],
        ],
        [
            'code'        => 'BD-15',
            'deliverable' => 'Strengthen Community Animal Health Delivery',
            'indicators'  => ['BOI-38', 'BOI-39'],
        ],
        [
            'code'        => 'BD-16',
            'deliverable' => 'Implement key components in National Dairy Policy',
            'indicators'  => ['BOI-40', 'BOI-41', 'BOI-42', 'BOI-43'],
        ],
    ];

    public function run(): void
    {
        $indicatorMap = Indicator::whereIn('code', collect($this->deliverables)->pluck('indicators')->flatten()->unique()->values()->toArray())
            ->pluck('id', 'code');

        foreach ($this->deliverables as $row) {
            $bd = BondDeliverable::firstOrCreate(
                ['code' => $row['code']],
                ['deliverable' => $row['deliverable']]
            );

            $ids = collect($row['indicators'])
                ->map(fn($c) => $indicatorMap[$c] ?? null)
                ->filter()
                ->values()
                ->toArray();

            if (!empty($ids)) {
                $bd->indicators()->syncWithoutDetaching($ids);
            }
        }

        $this->command->info('Bond Deliverables seeded: ' . BondDeliverable::count());
    }
}
