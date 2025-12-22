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
        Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('customer_name')->nullable()->after('user_id');
            $table->string('customer_phone', 20)->nullable()->after('customer_name');
            $table->boolean('satisfied')->comment('1 = Yes, 0 = No');
            $table->string('issue_type')->nullable();
            $table->text('message')->nullable();
            $table->string('source')->default('app');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};
