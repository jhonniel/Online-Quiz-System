<?php

namespace App\Console\Commands;

use App\Services\LeaveRequestIncompleteAttendanceOvertimeService;
use Illuminate\Console\Command;

class LeaveRequestsAutoRejectIncompleteAttendanceOvertimeCommand extends Command
{
    protected $signature = 'leave-requests:auto-reject-incomplete-attendance-overtime';

    protected $description = 'Reject pending overtime leave requests from Record Attendance when details were not completed in time';

    public function handle(LeaveRequestIncompleteAttendanceOvertimeService $service): int
    {
        $n = $service->processDueAutoRejects();
        $this->info("Auto-rejected {$n} incomplete attendance overtime leave request(s).");

        return self::SUCCESS;
    }
}
