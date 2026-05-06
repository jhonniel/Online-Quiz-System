<?php

namespace App\Console\Commands;

use App\Services\LeaveRequestStaleResubmissionService;
use Illuminate\Console\Command;

class LeaveRequestsAutoRejectStaleResubmissionsCommand extends Command
{
    protected $signature = 'leave-requests:auto-reject-stale-resubmissions';

    protected $description = 'Reject pending leave requests whose user did not respond within the resubmission deadline';

    public function handle(LeaveRequestStaleResubmissionService $service): int
    {
        $n = $service->processDueAutoRejects();
        $this->info("Auto-rejected {$n} stale resubmission leave request(s).");

        return self::SUCCESS;
    }
}
