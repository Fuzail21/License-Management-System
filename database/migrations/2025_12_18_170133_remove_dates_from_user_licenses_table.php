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
        Schema::table('user_licenses', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'expiry_date', 'renewal_cycle']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_licenses', function (Blueprint $table) {
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('renewal_cycle')->nullable();
        });
    }
};
