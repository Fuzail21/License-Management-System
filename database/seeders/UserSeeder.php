<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $staffRole = Role::where('name', 'Staff')->first();

        $users = [
            [
                'role_id' => $adminRole->id,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
            [
                'role_id' => $managerRole->id,
                'name' => 'John Manager',
                'email' => 'manager1@example.com',
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
            [
                'role_id' => $managerRole->id,
                'name' => 'Jane Manager',
                'email' => 'manager2@example.com',
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
            [
                'role_id' => $staffRole->id,
                'name' => 'Staff Member',
                'email' => 'staff@example.com',
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
            [
                'role_id' => $staffRole->id,
                'name' => 'Inactive Staff',
                'email' => 'inactive@example.com',
                'password' => Hash::make('password'),
                'status' => 'inactive',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
