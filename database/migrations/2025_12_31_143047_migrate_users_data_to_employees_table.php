<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration copies all user data to the employees table
     * and updates related tables to reference employees instead of users.
     *
     * IMPORTANT: Users and Employees are INDEPENDENT - no user_id foreign key exists.
     */
    public function up(): void
    {
        // Step 1: Copy all users to employees table
        $users = DB::table('users')->get();

        $userToEmployeeMap = [];

        foreach ($users as $user) {
            $employeeId = DB::table('employees')->insertGetId([
                'department_id' => $user->department_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'designation' => $user->designation,
                'status' => $user->status,
                'head' => $user->head ?? 0,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

            // Store mapping: user_id => employee_id
            $userToEmployeeMap[$user->id] = $employeeId;
        }

        // Step 2: Update user_licenses table
        foreach ($userToEmployeeMap as $userId => $employeeId) {
            DB::table('user_licenses')
                ->where('user_id', $userId)
                ->update(['employee_id' => $employeeId]);
        }

        // Step 3: Update user_feedback table
        foreach ($userToEmployeeMap as $userId => $employeeId) {
            DB::table('user_feedback')
                ->where('user_id', $userId)
                ->update(['employee_id' => $employeeId]);
        }

        // Step 4: Update license_renewals table
        // Note: renewed_by is currently a string/varchar, so we match by user_id
        foreach ($userToEmployeeMap as $userId => $employeeId) {
            DB::table('license_renewals')
                ->where('renewed_by', $userId)
                ->update(['renewed_by_employee_id' => $employeeId]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * This will clear all employee data and reset the employee_id columns.
     */
    public function down(): void
    {
        // Clear employee references from related tables
        DB::table('user_licenses')->update(['employee_id' => null]);
        DB::table('user_feedback')->update(['employee_id' => null]);
        DB::table('license_renewals')->update(['renewed_by_employee_id' => null]);

        // Delete all employees (using delete instead of truncate to avoid FK constraint issues)
        DB::table('employees')->delete();
    }
};
