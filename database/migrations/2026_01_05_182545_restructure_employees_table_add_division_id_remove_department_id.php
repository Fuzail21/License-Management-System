<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Drop index on status column
        if (Schema::hasColumn('employees', 'status')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });
        }

        // Step 2: Drop constraints using raw SQL
        \DB::statement("DECLARE @ConstraintName nvarchar(200)
            SELECT @ConstraintName = Name FROM SYS.DEFAULT_CONSTRAINTS
            WHERE PARENT_OBJECT_ID = OBJECT_ID('employees')
            AND PARENT_COLUMN_ID = (SELECT column_id FROM sys.columns
                                    WHERE NAME = N'status'
                                    AND object_id = OBJECT_ID(N'employees'))
            IF @ConstraintName IS NOT NULL
            EXEC('ALTER TABLE employees DROP CONSTRAINT ' + @ConstraintName)");

        \DB::statement("DECLARE @CheckConstraintName nvarchar(200)
            SELECT @CheckConstraintName = cc.name
            FROM sys.check_constraints cc
            INNER JOIN sys.columns c ON c.object_id = cc.parent_object_id
            WHERE cc.parent_object_id = OBJECT_ID('employees')
            AND c.name = 'status'
            IF @CheckConstraintName IS NOT NULL
            EXEC('ALTER TABLE employees DROP CONSTRAINT ' + @CheckConstraintName)");

        // Step 3: Add new columns as nullable first (SQL Server requirement for non-empty tables)
        Schema::table('employees', function (Blueprint $table) {
            // Add employee_number for unique identification (nullable first)
            if (!Schema::hasColumn('employees', 'employee_number')) {
                $table->string('employee_number')->nullable()->after('id');
            }

            // Add division_id (employees belong to divisions)
            // Using NO ACTION to avoid SQL Server cascade path conflicts
            if (!Schema::hasColumn('employees', 'division_id')) {
                $table->foreignId('division_id')->nullable()->after('id');
            }

            // Add first_name and last_name as nullable first
            if (Schema::hasColumn('employees', 'name') && !Schema::hasColumn('employees', 'first_name')) {
                $table->string('first_name')->nullable()->after('division_id');
                $table->string('last_name')->nullable()->after('first_name');
            }

            // Add hire_date
            if (!Schema::hasColumn('employees', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('phone');
            }
        });

        // Step 4: Migrate data from 'name' to 'first_name' and 'last_name'
        if (Schema::hasColumn('employees', 'name')) {
            \DB::statement("
                UPDATE employees
                SET first_name = CASE
                    WHEN CHARINDEX(' ', name) > 0
                    THEN LEFT(name, CHARINDEX(' ', name) - 1)
                    ELSE name
                END,
                last_name = CASE
                    WHEN CHARINDEX(' ', name) > 0
                    THEN SUBSTRING(name, CHARINDEX(' ', name) + 1, LEN(name))
                    ELSE ''
                END
                WHERE name IS NOT NULL
            ");

            // Generate unique employee_numbers for existing records
            \DB::statement("
                UPDATE employees
                SET employee_number = 'EMP' + RIGHT('00000' + CAST(id as VARCHAR), 5)
                WHERE employee_number IS NULL
            ");
        }

        // Step 5: Make columns NOT NULL and drop old columns
        Schema::table('employees', function (Blueprint $table) {
            // Remove department_id (employees belong to divisions, not departments directly)
            if (Schema::hasColumn('employees', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            }

            // Drop the old 'name' column
            if (Schema::hasColumn('employees', 'name')) {
                $table->dropColumn('name');
            }

            // Remove 'head' and 'designation' columns
            if (Schema::hasColumn('employees', 'head')) {
                $table->dropColumn('head');
            }
            if (Schema::hasColumn('employees', 'designation')) {
                $table->dropColumn('designation');
            }

            // Drop and recreate status column
            if (Schema::hasColumn('employees', 'status')) {
                $table->dropColumn('status');
            }
            $table->enum('status', ['active', 'inactive', 'terminated', 'on_leave'])->default('active')->after('hire_date');
            $table->index('status');
        });

        // Step 6: Make first_name and employee_number NOT NULL and add unique constraint
        \DB::statement("ALTER TABLE employees ALTER COLUMN first_name nvarchar(255) NOT NULL");
        \DB::statement("ALTER TABLE employees ALTER COLUMN last_name nvarchar(255) NOT NULL");
        \DB::statement("ALTER TABLE employees ALTER COLUMN employee_number nvarchar(255) NOT NULL");

        // Add unique constraint to employee_number and foreign key to division_id
        Schema::table('employees', function (Blueprint $table) {
            $table->unique('employee_number');
            $table->index('division_id');
        });

        // Add foreign key constraint with NO ACTION (SQL Server requirement to avoid cascade conflicts)
        \DB::statement("ALTER TABLE employees ADD CONSTRAINT employees_division_id_foreign
            FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE NO ACTION ON UPDATE NO ACTION");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Remove division_id
            $table->dropForeign(['division_id']);
            $table->dropIndex(['division_id']);
            $table->dropColumn('division_id');

            // Re-add department_id
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->index('department_id');

            // Revert name changes
            if (!Schema::hasColumn('employees', 'name')) {
                $table->string('name')->after('department_id');
            }
            if (Schema::hasColumn('employees', 'first_name')) {
                $table->dropColumn(['first_name', 'last_name']);
            }

            // Remove employee_number
            if (Schema::hasColumn('employees', 'employee_number')) {
                $table->dropColumn('employee_number');
            }

            // Remove hire_date
            if (Schema::hasColumn('employees', 'hire_date')) {
                $table->dropColumn('hire_date');
            }

            // Re-add head and designation
            $table->string('designation')->nullable();
            $table->boolean('head')->default(0);

            // Revert status
            $table->dropColumn('status');
            $table->enum('status', ['active', 'inactive'])->default('active');
        });
    }
};
