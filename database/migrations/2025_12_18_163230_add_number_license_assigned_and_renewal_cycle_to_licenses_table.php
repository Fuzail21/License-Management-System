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
        Schema::table('licenses', function (Blueprint $table) {
            $table->unsignedInteger('number_license_assigned')
                  ->default(0)
                  ->after('max_users');

            $table->string('renewal_cycle')
                  ->nullable()
                  ->comment('e.g. monthly, yearly, lifetime')
                  ->after('renewal_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn([
                'number_license_assigned',
                'renewal_cycle'
            ]);
        });
    }
};
