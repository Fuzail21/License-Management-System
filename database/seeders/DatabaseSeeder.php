<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in correct order to respect foreign key constraints
        $this->call([
            // 1. Roles (no dependencies)
            RoleSeeder::class,

            // 2. Cities (no dependencies)
            CitySeeder::class,

            // 3. Users (depends on roles)
            UserSeeder::class,

            // 4. City-Manager assignments (depends on cities and users)
            CityManagerSeeder::class,

            // 5. Departments (depends on cities)
            DepartmentSeeder::class,

            // 6. Divisions (depends on departments)
            DivisionSeeder::class,

            // 7. Employees (depends on divisions)
            EmployeeSeeder::class,
        ]);
    }
}
