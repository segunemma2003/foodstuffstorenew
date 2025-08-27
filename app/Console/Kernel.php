<?php

namespace App\Console;

use App\Jobs\RefreshCustomerQuotas;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:update-employee-salaries')->monthlyOn(1, '00:00');
        $schedule->job(new RefreshCustomerQuotas)->monthlyOn(1, '00:00');
        // $schedule->command('inspire')->hourly();
        // Add your scheduled tasks here.
    }

    /**
     * Register the commands for the application.
     */
    // protected function commands(): void
    // {
    //     $this->load(__DIR__.'/Commands');

    //     require base_path('routes/console.php');
    // }

    protected $commands = [
        \App\Console\Commands\UpdateEmployeeSalaries::class,
    ];
}
