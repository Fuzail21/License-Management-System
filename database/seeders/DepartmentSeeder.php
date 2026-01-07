<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $newYork = City::where('code', 'NYC')->first();
        $losAngeles = City::where('code', 'LAX')->first();
        $chicago = City::where('code', 'CHI')->first();

        $departments = [
            // New York departments
            [
                'city_id' => $newYork->id,
                'name' => 'Sales',
                'status' => 'active',
            ],
            [
                'city_id' => $newYork->id,
                'name' => 'Engineering',
                'status' => 'active',
            ],
            [
                'city_id' => $newYork->id,
                'name' => 'Human Resources',
                'status' => 'active',
            ],
            // Los Angeles departments
            [
                'city_id' => $losAngeles->id,
                'name' => 'Marketing',
                'status' => 'active',
            ],
            [
                'city_id' => $losAngeles->id,
                'name' => 'Customer Support',
                'status' => 'active',
            ],
            // Chicago departments
            [
                'city_id' => $chicago->id,
                'name' => 'Finance',
                'status' => 'active',
            ],
            [
                'city_id' => $chicago->id,
                'name' => 'Operations',
                'status' => 'inactive',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
