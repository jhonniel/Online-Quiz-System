<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->uuid('teacher_excused_batch')->nullable()->after('attendance_submission_batch');
            $table->index('teacher_excused_batch');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex(['teacher_excused_batch']);
            $table->dropColumn('teacher_excused_batch');
        });
    }
};
