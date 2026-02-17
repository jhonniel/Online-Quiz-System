<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('dtrs')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Update the CHECK constraint
            DB::statement('ALTER TABLE dtrs DROP CONSTRAINT IF EXISTS dtrs_status_check');
            DB::statement("ALTER TABLE dtrs ADD CONSTRAINT dtrs_status_check CHECK (status IN ('present', 'absent', 'late', 'half_day', 'on_leave', 'travel'))");
        } elseif ($driver === 'mysql') {
            // MySQL: Modify the ENUM column
            DB::statement("ALTER TABLE dtrs MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day', 'on_leave', 'travel') DEFAULT 'present'");
        } else {
            // SQLite: Recreate table (SQLite doesn't support ALTER COLUMN)
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

            DB::statement('
                INSERT INTO dtrs_temp (id, user_id, date, time_in, time_out, break_start, break_end, total_hours, overtime_hours, remarks, status, added_time_from_note, created_at, updated_at)
                SELECT id, user_id, date, time_in, time_out, break_start, break_end, total_hours, overtime_hours, remarks, status, added_time_from_note, created_at, updated_at
                FROM dtrs
            ');

            Schema::drop('dtrs');
            Schema::rename('dtrs_temp', 'dtrs');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('dtrs')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Revert the CHECK constraint
            DB::statement('ALTER TABLE dtrs DROP CONSTRAINT IF EXISTS dtrs_status_check');
            DB::statement("ALTER TABLE dtrs ADD CONSTRAINT dtrs_status_check CHECK (status IN ('present', 'absent', 'late', 'half_day', 'on_leave'))");
        } elseif ($driver === 'mysql') {
            // MySQL: Revert the ENUM column
            DB::statement("ALTER TABLE dtrs MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day', 'on_leave') DEFAULT 'present'");
        } else {
            // SQLite: Recreate table
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

            DB::statement("
                INSERT INTO dtrs_original (id, user_id, date, time_in, time_out, break_start, break_end, total_hours, overtime_hours, remarks, status, added_time_from_note, created_at, updated_at)
                SELECT id, user_id, date, time_in, time_out, break_start, break_end, total_hours, overtime_hours, remarks, status, added_time_from_note, created_at, updated_at
                FROM dtrs
                WHERE status != 'travel'
            ");

            Schema::drop('dtrs');
            Schema::rename('dtrs_original', 'dtrs');
        }
    }
};
