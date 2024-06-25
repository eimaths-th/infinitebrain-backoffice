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
        // $schedule->command('export:database')->everyFiveMinutes();
        $schedule->command('updateStatus:student')->dailyAt('07.00'); // for test
        //updated
        //updated
        $schedule->command('export:database')->dailyAt('19.00');
        //test
        // $schedule->command('updateStatus:student')->everyTwoMinutes()->appendOutputTo('/storage/logs/worker.log'); // for test
        // $schedule->command('updateStatus2:student')->everyMinute(); // for test
    }
    // chmod -R 775 /var/www/html/infinite_backoffice/storage/logs
    // chown -R manon:manon /var/www/html/infinite_backoffice/storage/logs
    // touch /var/www/html/infinite_backoffice/storage/logs/worker.log
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
