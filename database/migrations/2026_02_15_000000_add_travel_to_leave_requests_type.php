<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'travel' to leave_requests.type allowed values.
     */
    public function up(): void
    {
        if (! Schema::hasTable('leave_requests')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $allowedTypes = ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset', 'additional_time', 'other', 'travel'];

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_type_check');
            DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_type_check CHECK (type IN ('" . implode("', '", $allowedTypes) . "'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('" . implode("', '", $allowedTypes) . "') DEFAULT 'vacation_leave'");
        } else {
            // SQLite: recreate table with new enum values (include supporting_document_path if present)
            $hasSupportingDoc = Schema::hasColumn('leave_requests', 'supporting_document_path');

            Schema::create('leave_requests_new', function (Blueprint $table) use ($allowedTypes, $hasSupportingDoc) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', $allowedTypes)->default('vacation_leave');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->text('reason')->nullable();
                if ($hasSupportingDoc) {
                    $table->string('supporting_document_path')->nullable();
                }
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('type');
            });

            $cols = $hasSupportingDoc
                ? 'id, user_id, type, start_date, end_date, reason, supporting_document_path, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at'
                : 'id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at';
            DB::statement("
                INSERT INTO leave_requests_new ({$cols})
                SELECT {$cols}
                FROM leave_requests
            ");

            Schema::drop('leave_requests');
            Schema::rename('leave_requests_new', 'leave_requests');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('leave_requests')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $allowedTypes = ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset', 'additional_time', 'other'];

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_type_check');
            DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_type_check CHECK (type IN ('" . implode("', '", $allowedTypes) . "'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('" . implode("', '", $allowedTypes) . "') DEFAULT 'vacation_leave'");
        } else {
            $hasSupportingDoc = Schema::hasColumn('leave_requests', 'supporting_document_path');

            Schema::create('leave_requests_rollback', function (Blueprint $table) use ($allowedTypes, $hasSupportingDoc) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', $allowedTypes)->default('vacation_leave');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->text('reason')->nullable();
                if ($hasSupportingDoc) {
                    $table->string('supporting_document_path')->nullable();
                }
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('type');
            });

            $cols = $hasSupportingDoc
                ? 'id, user_id, type, start_date, end_date, reason, supporting_document_path, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at'
                : 'id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at';
            DB::statement("
                INSERT INTO leave_requests_rollback ({$cols})
                SELECT {$cols}
                FROM leave_requests
                WHERE type != 'travel'
            ");

            Schema::drop('leave_requests');
            Schema::rename('leave_requests_rollback', 'leave_requests');
        }
    }
};
