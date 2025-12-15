<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Setting::count() === 0) {
            Setting::create([
                'app_name' => 'License Management System',
                'primary_color' => '#4f46e5', // Indigo-600
                'secondary_color' => '#ec4899', // Pink-500
                'currency_name' => 'USD',
                'currency_symbol' => '$',
                'timezone' => 'UTC',
                'footer_text' => '© ' . date('Y') . ' License Management System. All rights reserved.',
            ]);
        }
    }
}
