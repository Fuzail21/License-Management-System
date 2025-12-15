<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have at least one department
        $department = Department::where('status', 'active')->first();

        if (!$department) {
            $department = Department::create([
                'name' => 'IT Department',
                'status' => 'active',
            ]);
        }

        // Create admin user if doesn't exist
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => 'password', // Will be auto-hashed
                'department_id' => $department->id,
                'designation' => 'System Administrator',
                'phone' => '+92-300-1234567',
                'status' => UserStatus::Active,
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@example.com');
            $this->command->info('Password: password');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
