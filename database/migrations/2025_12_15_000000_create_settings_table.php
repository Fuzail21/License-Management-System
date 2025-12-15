<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('License Management System');
            $table->string('app_logo')->nullable();
            $table->string('primary_color')->default('#4f46e5'); // Indigo-600
            $table->string('secondary_color')->default('#ec4899'); // Pink-500
            $table->string('currency_name')->default('USD');
            $table->string('currency_symbol')->default('$');
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->text('address')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('timezone')->default('UTC');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
