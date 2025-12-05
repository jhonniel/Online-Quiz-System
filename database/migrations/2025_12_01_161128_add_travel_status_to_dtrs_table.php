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
     * This migration rebuilds the dtrs table to add the "travel"
     * value to the status enum while preserving existing data. It is written
     * in a SQLite‑friendly way (create temp table → copy → swap).
     */
    public function up(): void
    {
        // If the table doesn't exist yet, nothing to update.
        if (! Schema::hasTable('dtrs')) {
            return;
        }

        // Create a temporary table with the updated enum definition
        Schema::create('dtrs_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave', 'travel'])->default('present');
            $table->text('added_time_from_note')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'date']);
            $table->unique(['user_id', 'date']);
        });

        // Copy data from old table into new table
        DB::statement('
            INSERT INTO dtrs_temp (id, user_id, date, time_in, time_out, break_start, break_end, total_hours, overtime_hours, remarks, status, added_time_from_note, created_at, updated_at)
            SELECT id, user_id, date, time_in, time_out, break_start, break_end, total_hours, overtime_hours, remarks, status, added_time_from_note, created_at, updated_at
            FROM dtrs
        ');

        // Drop the old table and rename the temp one
        Schema::drop('dtrs');
        Schema::rename('dtrs_temp', 'dtrs');
    }

    /**
     * Reverse the migrations.
     *
     * This recreates the table without "travel" in the enum. Any rows with
     * status = "travel" would fail to copy back, so use with care.
     */
    public function down(): void
    {
        if (! Schema::hasTable('dtrs')) {
            return;
        }

        Schema::create('dtrs_original', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave'])->default('present');
            $table->text('added_time_from_note')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'date']);
            $table->unique(['user_id', 'date']);
        });

        // Copy data back (excluding any rows with status = 'travel')
        DB::statement('
            INSERT INTO dtrs_original (id, user_id, date, time_in, time_out, break_start, break_end, total_hours, overtime_hours, remarks, status, added_time_from_note, created_at, updated_at)
            SELECT id, user_id, date, time_in, time_out, break_start, break_end, total_hours, overtime_hours, remarks, status, added_time_from_note, created_at, updated_at
            FROM dtrs
            WHERE status != 'travel'
        ');

        Schema::drop('dtrs');
        Schema::rename('dtrs_original', 'dtrs');
    }
};
