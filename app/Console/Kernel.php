<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Say-it: delete posts with no likes and no comments after 7 days from post date
        $schedule->command('sayit:delete-unengaged')
            ->hourly()
            ->withoutOverlapping();

        $schedule->command('students:process-ojt-post-completion')
            ->dailyAt('06:30')
            ->withoutOverlapping();

        $schedule->command('leave-requests:auto-reject-stale-resubmissions')
            ->hourly()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }
}
