<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        /*
            Run the following cron job inside Docker app container
        */
        $schedule->command('model:prune')
            /* 00:00 == 12AM */
            // ->dailyAt('00:00')
            ->everySecond()
            ->withoutOverlapping()
            ->runInBackground()
            ->emailOutputOnFailure(config('constants.ADMIN_EMAIL'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
