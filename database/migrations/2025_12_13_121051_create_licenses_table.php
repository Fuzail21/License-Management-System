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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->string('license_name');
            $table->enum('license_type', ['subscription', 'perpetual']);
            $table->string('version')->nullable();
            $table->integer('max_users')->nullable();
            $table->decimal('cost', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('license_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
