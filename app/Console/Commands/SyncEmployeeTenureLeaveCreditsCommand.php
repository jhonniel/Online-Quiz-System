<?php

namespace App\Console\Commands;

use App\Support\EmployeeTenureLeaveCredits;
use Illuminate\Console\Command;

class SyncEmployeeTenureLeaveCreditsCommand extends Command
{
    protected $signature = 'employees:sync-tenure-leave-credits';

    protected $description = 'Apply automatic tenure leave credits for enabled active employees who reached 6 months or 1 year from Date Hired.';

    public function handle(): int
    {
        $stats = EmployeeTenureLeaveCredits::syncAllEnabled();

        $this->info('Checked '.$stats['checked'].' enabled employee(s).');
        $this->line('Applied tenure leave credits: '.$stats['applied']);

        return self::SUCCESS;
    }
}
