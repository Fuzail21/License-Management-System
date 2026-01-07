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
            // Add status column for approval workflow
            // pending = awaiting admin approval
            // approved = active license (default for admin/authorized managers)
            // rejected = admin declined the request
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('approved')
                  ->after('license_key');

            // Track who created the license (for audit trail)
            $table->foreignId('created_by')->nullable()->after('vendor_id');

            // Track who approved/rejected the license
            $table->foreignId('approved_by')->nullable()->after('created_by');

            // Timestamp of approval/rejection
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            // Optional rejection reason (admin feedback)
            $table->text('rejection_reason')->nullable()->after('approved_at');

            // Index for faster filtering by status
            $table->index('status');
        });

        // Add foreign key constraints with NO ACTION (SQL Server requirement to avoid cascade conflicts)
        \DB::statement("ALTER TABLE licenses ADD CONSTRAINT licenses_created_by_foreign
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE NO ACTION ON UPDATE NO ACTION");

        \DB::statement("ALTER TABLE licenses ADD CONSTRAINT licenses_approved_by_foreign
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE NO ACTION ON UPDATE NO ACTION");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'created_by',
                'approved_by',
                'approved_at',
                'rejection_reason'
            ]);
        });
    }
};
