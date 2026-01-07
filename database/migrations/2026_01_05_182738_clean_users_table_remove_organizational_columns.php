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
        // Step 1: Drop index on status column if exists
        if (Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });

            // Step 2: Drop constraints using raw SQL
            \DB::statement("DECLARE @ConstraintName nvarchar(200)
                SELECT @ConstraintName = Name FROM SYS.DEFAULT_CONSTRAINTS
                WHERE PARENT_OBJECT_ID = OBJECT_ID('users')
                AND PARENT_COLUMN_ID = (SELECT column_id FROM sys.columns
                                        WHERE NAME = N'status'
                                        AND object_id = OBJECT_ID(N'users'))
                IF @ConstraintName IS NOT NULL
                EXEC('ALTER TABLE users DROP CONSTRAINT ' + @ConstraintName)");

            \DB::statement("DECLARE @CheckConstraintName nvarchar(200)
                SELECT @CheckConstraintName = cc.name
                FROM sys.check_constraints cc
                INNER JOIN sys.columns c ON c.object_id = cc.parent_object_id
                WHERE cc.parent_object_id = OBJECT_ID('users')
                AND c.name = 'status'
                IF @CheckConstraintName IS NOT NULL
                EXEC('ALTER TABLE users DROP CONSTRAINT ' + @CheckConstraintName)");
        }

        // Step 3: Make all the schema changes
        Schema::table('users', function (Blueprint $table) {
            // Remove department_id (users should not belong to departments)
            if (Schema::hasColumn('users', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            }

            // Remove phone, designation (not needed for system users)
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('users', 'designation')) {
                $table->dropColumn('designation');
            }

            // Remove head column (not relevant for users)
            if (Schema::hasColumn('users', 'head')) {
                $table->dropColumn('head');
            }

            // Drop and recreate status column with new values
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('password');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-add department_id
            $table->foreignId('department_id')->nullable()->after('id')->constrained('departments')->onDelete('cascade');
            $table->index('department_id');

            // Re-add phone and designation
            $table->string('phone')->nullable()->after('email');
            $table->string('designation')->nullable()->after('phone');

            // Re-add head
            $table->boolean('head')->default(0);

            // Revert status
            $table->dropIndex(['status']);
            $table->dropColumn('status');
            $table->enum('status', ['active', 'inactive'])->default('active');
        });
    }
};
