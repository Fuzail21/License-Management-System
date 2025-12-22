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
        Schema::create('license_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            $table->date('old_expiry_date')->nullable();
            $table->date('new_expiry_date');
            $table->decimal('renewal_cost', 10, 2);
            $table->string('renewed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('renewed_at');
            $table->timestamps();

            $table->index('renewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_renewals');
    }
};
