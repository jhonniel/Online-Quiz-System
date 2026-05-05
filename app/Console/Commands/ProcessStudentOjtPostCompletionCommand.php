<?php

namespace App\Console\Commands;

use App\Services\StudentOjtPostCompletionService;
use Illuminate\Console\Command;

class ProcessStudentOjtPostCompletionCommand extends Command
{
    protected $signature = 'students:process-ojt-post-completion';

    protected $description = 'For students: detect completed OJT hours, send congratulations, and disable accounts after the post-completion grace period.';

    public function handle(StudentOjtPostCompletionService $service): int
    {
        $n = $service->processAllStudents();
        $this->info("Processed {$n} student account(s).");

        return self::SUCCESS;
    }
}
