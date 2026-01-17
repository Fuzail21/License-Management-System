<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration swaps the names of departments and divisions tables:
     * - Old "departments" table (linked to cities) -> New "divisions" table
     * - Old "divisions" table (linked to departments) -> New "departments" table
     *
     * Current structure:
     * cities -> departments (city_id) -> divisions (department_id) -> employees (division_id)
     *
     * After swap:
     * cities -> divisions (city_id) -> departments (division_id) -> employees (department_id)
     */
    public function up(): void
    {
        // For SQL Server, we need to use sp_rename
        // Step 1: Rename departments -> divisions_temp
        DB::statement("EXEC sp_rename 'departments', 'divisions_temp'");

        // Step 2: Rename divisions -> departments_temp
        DB::statement("EXEC sp_rename 'divisions', 'departments_temp'");

        // Step 3: Rename divisions_temp -> divisions (was departments)
        DB::statement("EXEC sp_rename 'divisions_temp', 'divisions'");

        // Step 4: Rename departments_temp -> departments (was divisions)
        DB::statement("EXEC sp_rename 'departments_temp', 'departments'");

        // Now update foreign key column names:

        // In new "departments" table (was divisions): department_id -> division_id
        DB::statement("EXEC sp_rename 'departments.department_id', 'division_id', 'COLUMN'");

        // In "employees" table: division_id -> department_id
        DB::statement("EXEC sp_rename 'employees.division_id', 'department_id', 'COLUMN'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the column renames first
        DB::statement("EXEC sp_rename 'employees.department_id', 'division_id', 'COLUMN'");
        DB::statement("EXEC sp_rename 'departments.division_id', 'department_id', 'COLUMN'");

        // Reverse the table swaps
        DB::statement("EXEC sp_rename 'departments', 'divisions_temp'");
        DB::statement("EXEC sp_rename 'divisions', 'departments_temp'");
        DB::statement("EXEC sp_rename 'divisions_temp', 'divisions'");
        DB::statement("EXEC sp_rename 'departments_temp', 'departments'");
    }
};
