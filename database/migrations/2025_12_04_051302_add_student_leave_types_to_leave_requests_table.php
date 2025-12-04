<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration rebuilds the leave_requests table to add "additional_time"
     * and "other" values to the type enum while preserving existing data.
     */
    public function up(): void
    {
        // If the table doesn't exist yet, nothing to update.
        if (! Schema::hasTable('leave_requests')) {
            return;
        }

        // Drop temp table if it exists (using raw SQL to handle SQLite)
        try {
            DB::statement('DROP TABLE IF EXISTS leave_requests_temp_new');
        } catch (\Exception $e) {
            // Ignore if table doesn't exist
        }

        // Create a temporary table with the updated enum definition
        Schema::create('leave_requests_temp_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset', 'additional_time', 'other'])->default('vacation_leave');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('type');
        });

        // Copy data from old table into new table
        DB::statement('
            INSERT INTO leave_requests_temp_new (id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at)
            SELECT id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at
            FROM leave_requests
        ');

        // Drop the old table and rename the temp one
        Schema::drop('leave_requests');
        Schema::rename('leave_requests_temp_new', 'leave_requests');
    }

    /**
     * Reverse the migrations.
     *
     * This recreates the table without "additional_time" and "other" in the enum.
     * Any rows with these types would fail to copy back, so use with care.
     */
    public function down(): void
    {
        if (! Schema::hasTable('leave_requests')) {
            return;
        }

        Schema::create('leave_requests_original', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'])->default('vacation_leave');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('type');
        });

        DB::statement('
            INSERT INTO leave_requests_original (id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at)
            SELECT id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at
            FROM leave_requests
            WHERE type NOT IN (\'additional_time\', \'other\')
        ');

        Schema::drop('leave_requests');
        Schema::rename('leave_requests_original', 'leave_requests');
    }
};
