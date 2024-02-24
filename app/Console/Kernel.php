<?php

namespace App\Console;

use App\Models\Student;
use App\Services\StatusService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        //$schedule->command('export:database')->dailyAt('16:45');//now 16.10
        // $schedule->command('export:database')->everyMinute();
        $schedule->command('export:database')->dailyAt('19.00');
        $schedule->call(function () {
            $students = Student::with('rec_school')->get();
            $statusService = app(StatusService::class);
            $statusService->updateStatus($students);
        })->everyMinute(); // for test
        // })->dailyAt('7:00');
    }

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
