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
        Schema::table('employees', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['department_id']);

            // Modify column to be nullable
            $table->foreignId('department_id')->nullable()->change();

            // Re-add foreign key constraint
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['department_id']);

            // Make column not nullable again
            $table->foreignId('department_id')->nullable(false)->change();

            // Re-add foreign key
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }
};
