<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            [
                'name' => 'New York',
                'code' => 'NYC',
                'status' => 'active',
            ],
            [
                'name' => 'Los Angeles',
                'code' => 'LAX',
                'status' => 'active',
            ],
            [
                'name' => 'Chicago',
                'code' => 'CHI',
                'status' => 'active',
            ],
            [
                'name' => 'Houston',
                'code' => 'HOU',
                'status' => 'active',
            ],
            [
                'name' => 'Phoenix',
                'code' => 'PHX',
                'status' => 'inactive',
            ],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}
