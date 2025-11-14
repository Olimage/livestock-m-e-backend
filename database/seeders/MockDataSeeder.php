<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MokData;

class MockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing mock data to avoid duplicates
        MokData::whereIn('name', ['stat_recordsSaved', 'stat_dataPendingSync'])->delete();

        // Seed dashboard statistics
        $stats = [
            [
                'name' => 'stat_recordsSaved',
                'value' => 15234
            ],
            [
                'name' => 'stat_dataPendingSync',
                'value' => 487
            ]
        ];

        foreach ($stats as $stat) {
            MokData::create($stat);
        }
    }
}
