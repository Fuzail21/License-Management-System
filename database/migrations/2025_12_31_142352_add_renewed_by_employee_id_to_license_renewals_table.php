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
        Schema::table('license_renewals', function (Blueprint $table) {
            $table->foreignId('renewed_by_employee_id')->nullable()->after('renewed_by')->constrained('employees')->onDelete('set null');
            $table->index('renewed_by_employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('license_renewals', function (Blueprint $table) {
            $table->dropForeign(['renewed_by_employee_id']);
            $table->dropIndex(['renewed_by_employee_id']);
            $table->dropColumn('renewed_by_employee_id');
        });
    }
};
