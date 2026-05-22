<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add 'leave' to leave_requests.type allowed values (used by admin type correction and DTR credits).
     */
    public function up(): void
    {
        if (! Schema::hasTable('leave_requests')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $allowedTypes = [
            'leave',
            'vacation_leave',
            'sick_leave',
            'work_from_home',
            'absent',
            'overtime',
            'offset',
            'additional_time',
            'other',
            'travel',
        ];

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_type_check');
            DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_type_check CHECK (type IN ('".implode("', '", $allowedTypes)."'))");

            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('".implode("', '", $allowedTypes)."') DEFAULT 'vacation_leave'");

            return;
        }

        $this->recreateLeaveRequestsTableForSqlite($allowedTypes);
    }

    public function down(): void
    {
        if (! Schema::hasTable('leave_requests')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $allowedTypes = [
            'vacation_leave',
            'sick_leave',
            'work_from_home',
            'absent',
            'overtime',
            'offset',
            'additional_time',
            'other',
            'travel',
        ];

        if ($driver === 'pgsql') {
            DB::table('leave_requests')->where('type', 'leave')->update(['type' => 'vacation_leave']);
            DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_type_check');
            DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_type_check CHECK (type IN ('".implode("', '", $allowedTypes)."'))");

            return;
        }

        if ($driver === 'mysql') {
            DB::table('leave_requests')->where('type', 'leave')->update(['type' => 'vacation_leave']);
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('".implode("', '", $allowedTypes)."') DEFAULT 'vacation_leave'");

            return;
        }

        DB::table('leave_requests')->where('type', 'leave')->update(['type' => 'vacation_leave']);
        $this->recreateLeaveRequestsTableForSqlite($allowedTypes);
    }

    /**
     * @param  list<string>  $allowedTypes
     */
    private function recreateLeaveRequestsTableForSqlite(array $allowedTypes): void
    {
        Schema::dropIfExists('leave_requests_new');

        $hasSupportingDoc = Schema::hasColumn('leave_requests', 'supporting_document_path');
        $hasSupportingDocs = Schema::hasColumn('leave_requests', 'supporting_document_paths');
        $hasTravelHours = Schema::hasColumn('leave_requests', 'travel_hours');

        Schema::create('leave_requests_new', function (Blueprint $table) use ($allowedTypes, $hasSupportingDoc, $hasSupportingDocs, $hasTravelHours) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', $allowedTypes)->default('vacation_leave');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('reason')->nullable();
            if ($hasSupportingDoc) {
                $table->string('supporting_document_path')->nullable();
            }
            if ($hasSupportingDocs) {
                $table->json('supporting_document_paths')->nullable();
            }
            if ($hasTravelHours) {
                $table->decimal('travel_hours', 8, 2)->nullable();
            }
            $table->enum('status', ['pending', 'approved', 'rejected', 'for_more_verification'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        $cols = ['id', 'user_id', 'type', 'start_date', 'end_date', 'reason'];
        if ($hasSupportingDoc) {
            $cols[] = 'supporting_document_path';
        }
        if ($hasSupportingDocs) {
            $cols[] = 'supporting_document_paths';
        }
        if ($hasTravelHours) {
            $cols[] = 'travel_hours';
        }
        $cols = array_merge($cols, ['status', 'admin_notes', 'reviewed_by', 'reviewed_at', 'created_at', 'updated_at']);
        $colList = implode(', ', $cols);

        DB::statement("INSERT INTO leave_requests_new ({$colList}) SELECT {$colList} FROM leave_requests");

        Schema::drop('leave_requests');
        Schema::rename('leave_requests_new', 'leave_requests');
    }
};
