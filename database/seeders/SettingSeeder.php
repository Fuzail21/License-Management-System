<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'app_name' => 'License Management System',
            'timezone' => 'Asia/Karachi',
            'date_format' => 'Y-m-d',
            'currency' => 'PKR',
            'logo' => null,
        ]);
    }
}
