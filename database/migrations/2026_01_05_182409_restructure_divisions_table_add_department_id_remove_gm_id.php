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
        Schema::table('divisions', function (Blueprint $table) {
            // Remove gm_id (divisions don't have general managers in new structure)
            if (Schema::hasColumn('divisions', 'gm_id')) {
                $table->dropForeign(['gm_id']);
                $table->dropIndex(['gm_id']);
                $table->dropColumn('gm_id');
            }

            // Add department_id (divisions belong to departments)
            $table->foreignId('department_id')->nullable()->after('id')
                  ->constrained('departments')->onDelete('cascade');
            $table->index('department_id');

            // Remove description to keep table normalized
            if (Schema::hasColumn('divisions', 'description')) {
                $table->dropColumn('description');
            }

            // Remove unique constraint from name (names can repeat across different departments)
            $table->dropUnique(['name']);

            // Add status column
            $table->enum('status', ['active', 'inactive'])->default('active')->after('name');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            // Remove department_id
            $table->dropForeign(['department_id']);
            $table->dropIndex(['department_id']);
            $table->dropColumn('department_id');

            // Re-add gm_id
            $table->foreignId('gm_id')->nullable()->constrained('users')->onDelete('set null');
            $table->index('gm_id');

            // Re-add description
            $table->text('description')->nullable();

            // Re-add unique constraint
            $table->unique('name');

            // Remove status
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
