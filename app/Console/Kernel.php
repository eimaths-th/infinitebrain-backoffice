<?php

namespace App\Console;

use App\Services\StatusService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */


    // protected $commands = [
    //     Commands\UpdateStudentStatusCommand ::class,
    // ];

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        //$schedule->command('export:database')->dailyAt('16:45');//now 16.10
        // $schedule->command('export:database')->everyTwoMinutes()->appendOutputTo('/storage/logs/worker.log');
        $schedule->command('export:database')->everyTwoMinutes();
        // $schedule->command('export:database')->dailyAt('19.00');
        // $schedule->command('updateStatus:student')->everyTwoMinutes()->appendOutputTo('/storage/logs/worker.log'); // for test
        $schedule->command('updateStatus:student')->everyTwoMinutes(); // for test
        // $schedule->command('updateStatus2:student')->everyMinute(); // for test

        // })->dailyAt('7:00');
    }
    // nohup /usr/bin/php /var/www/html/infinite_backoffice/artisan schedule:work > /var/www/html/infinite_backoffice/storage/logs/worker.log 2>&1 &
    // nohup /usr/bin/php /var/www/html/infinite_backoffice/artisan schedule:work > /var/www/html/infinite_backoffice/storage/logs/worker.log 2>&1 &

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
