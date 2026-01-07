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
        Schema::table('departments', function (Blueprint $table) {
            // Remove the incorrect division_id column (departments don't belong to divisions)
            if (Schema::hasColumn('departments', 'division_id')) {
                $table->dropForeign(['division_id']);
                $table->dropIndex(['division_id']);
                $table->dropColumn('division_id');
            }

            // Add city_id (departments belong to cities)
            $table->foreignId('city_id')->nullable()->after('id')
                  ->constrained('cities')->onDelete('set null');
            $table->index('city_id');

            // Remove description column to keep table normalized
            if (Schema::hasColumn('departments', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Remove city_id
            $table->dropForeign(['city_id']);
            $table->dropIndex(['city_id']);
            $table->dropColumn('city_id');

            // Re-add division_id (wrong relationship, but for rollback)
            $table->foreignId('division_id')->nullable()->after('id')
                  ->constrained('divisions')->onDelete('set null');
            $table->index('division_id');

            // Re-add description
            $table->text('description')->nullable();
        });
    }
};
