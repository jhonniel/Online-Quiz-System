<?php

namespace App\Console\Commands;

use App\Services\StudentOjtPostCompletionService;
use Illuminate\Console\Command;

class ProcessStudentOjtPostCompletionCommand extends Command
{
    protected $signature = 'students:process-ojt-post-completion';

    protected $description = 'Reconcile all students: backfill completed OJT hours (including existing accounts), apply grace period, and auto-disable when grace ends.';

    public function handle(StudentOjtPostCompletionService $service): int
    {
        $stats = $service->processAllStudents();

        $this->info('Processed '.$stats['processed'].' student account(s).');
        $this->line('Already completed required hours: '.$stats['completed_hours']);
        $this->line('Backfilled completion date (legacy): '.$stats['backfilled_met_at']);
        $this->line('Terminated after grace: '.$stats['terminated']);
        $this->line('Congratulations emails sent: '.$stats['congratulations_sent']);
        $this->line('Account-disabled emails sent: '.$stats['disabled_notice_sent']);

        return self::SUCCESS;
    }
}
