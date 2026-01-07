<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CityManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerRole = Role::where('name', 'Manager')->first();
        $managers = User::where('role_id', $managerRole->id)->get();

        $newYork = City::where('code', 'NYC')->first();
        $losAngeles = City::where('code', 'LAX')->first();
        $chicago = City::where('code', 'CHI')->first();

        // Assign first manager to New York and Los Angeles
        if ($managers->count() > 0) {
            $newYork->managers()->attach($managers[0]->id, ['assigned_at' => now()]);
            $losAngeles->managers()->attach($managers[0]->id, ['assigned_at' => now()]);
        }

        // Assign second manager to Chicago
        if ($managers->count() > 1) {
            $chicago->managers()->attach($managers[1]->id, ['assigned_at' => now()]);
        }
    }
}
