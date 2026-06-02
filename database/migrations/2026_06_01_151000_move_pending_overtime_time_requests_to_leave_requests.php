<?php

use App\Models\DtrTimeRequest;
use App\Models\LeaveRequest;
use App\Support\TimeRequestOvertimeLeaveImport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $legacy = DtrTimeRequest::query()
            ->where('request_type', 'overtime')
            ->where('status', 'pending')
            ->get();

        foreach ($legacy as $timeRequest) {
            $dateStr = $timeRequest->date?->format('Y-m-d');
            if (! $dateStr) {
                continue;
            }

            $batchId = $timeRequest->submission_batch ?? (string) Str::uuid();

            $exists = LeaveRequest::query()
                ->where('user_id', $timeRequest->user_id)
                ->where('type', 'overtime')
                ->whereDate('start_date', $dateStr)
                ->where('status', 'pending')
                ->exists();

            if (! $exists) {
                TimeRequestOvertimeLeaveImport::createPendingFromAttendance(
                    (int) $timeRequest->user_id,
                    $dateStr,
                    (float) $timeRequest->hours,
                    $timeRequest->requested_total_hours ? (float) $timeRequest->requested_total_hours : null,
                    $batchId,
                    $timeRequest->remarks
                );
            }

            $timeRequest->delete();
        }
    }

    public function down(): void
    {
        // Not reversible without losing linkage detail.
    }
};
