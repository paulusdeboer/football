<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cost per game setting
        Setting::updateOrCreate(
            ['key' => 'cost_per_game'],
            [
                'display_name' => __('Cost per game'),
                'value' => '4.00',
                'description' => __('Amount in euros that a player pays per game')
            ]
        );
    }
}
