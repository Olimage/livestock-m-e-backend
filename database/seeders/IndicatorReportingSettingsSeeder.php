<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

class IndicatorReportingSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the row exists with the boolean type and a label.
        Setting::updateOrCreate(
            ['key' => SettingService::ALLOW_SUPPORTING_DEPT],
            [
                'value' => '0',
                'type' => 'boolean',
                'label' => 'Allow supporting-department reporting',
                'description' => 'When on, users in an indicator\'s supporting departments may also report on it.',
            ],
        );
    }
}
