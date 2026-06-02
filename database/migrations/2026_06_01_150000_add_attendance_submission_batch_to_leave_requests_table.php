<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->uuid('attendance_submission_batch')->nullable()->after('dtr_time_request_id');
            $table->index(['user_id', 'attendance_submission_batch']);
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'attendance_submission_batch']);
            $table->dropColumn('attendance_submission_batch');
        });
    }
};
