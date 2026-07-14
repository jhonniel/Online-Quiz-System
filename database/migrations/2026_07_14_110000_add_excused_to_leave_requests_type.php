<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add admin-only 'excused' to leave_requests.type (student merit exclusion).
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
            'excused',
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
        }

        // SQLite: type is stored as TEXT; no enum rewrite required.
    }

    public function down(): void
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

        DB::table('leave_requests')->where('type', 'excused')->update(['type' => 'absent']);

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_type_check');
            DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_type_check CHECK (type IN ('".implode("', '", $allowedTypes)."'))");

            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('".implode("', '", $allowedTypes)."') DEFAULT 'vacation_leave'");
        }
    }
};
