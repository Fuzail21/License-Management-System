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
            $table->dropForeign(['user_license_id']);

            // 2. Now it is safe to drop the column
            $table->dropColumn('user_license_id');
            
            // 3. Add the new license_id column linked to the 'licenses' table
            $table->foreignId('license_id')
                  ->after('id')
                  ->constrained('licenses')
                  ->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('license_renewals', function (Blueprint $table) {
            $table->dropForeign(['license_id']);
            $table->dropColumn('license_id');
            $table->unsignedBigInteger('user_license_id')->after('id');
        });
    }
};
