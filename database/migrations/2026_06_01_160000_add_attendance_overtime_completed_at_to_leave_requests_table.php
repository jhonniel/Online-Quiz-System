<?php

use App\Support\TimeRequestOvertimeLeaveImport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->timestamp('attendance_overtime_completed_at')->nullable()->after('attendance_submission_batch');
        });

        // Existing attendance overtime rows with real task text and supporting files count as complete.
        DB::table('leave_requests')
            ->where('type', 'overtime')
            ->whereNotNull('attendance_submission_batch')
            ->whereNull('attendance_overtime_completed_at')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $reason = (string) ($row->reason ?? '');
                    $hasTasks = preg_match('/Tasks \/ ClickUp Links:\s*\S/s', $reason) === 1
                        && ! str_contains($reason, TimeRequestOvertimeLeaveImport::ATTENDANCE_STUB_TASKS)
                        && ! str_contains($reason, 'No ClickUp links provided in Record Attendance');

                    $paths = json_decode((string) ($row->supporting_document_paths ?? '[]'), true);
                    $hasDocs = is_array($paths) && count($paths) > 0;
                    $hasLegacyDoc = filled($row->supporting_document_path ?? null);
                    $hasExplanation = preg_match('/Additional Explanation:\s*\S/s', $reason) === 1
                        && ! str_contains($reason, 'filed via Record Attendance');

                    if ($hasTasks && ($hasDocs || $hasLegacyDoc) && $hasExplanation) {
                        DB::table('leave_requests')
                            ->where('id', $row->id)
                            ->update(['attendance_overtime_completed_at' => $row->updated_at ?? $row->created_at]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('attendance_overtime_completed_at');
        });
    }
};
