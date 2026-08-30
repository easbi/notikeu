<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

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
        $schedule->command('queue:work --once')
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        $schedule->command('morin:remind-missing-gaji-reference')
            ->dailyAt('08:00')
            ->withoutOverlapping();

        // Cron KGB - setiap tanggal 2 jam 07:00
        $schedule->command('kgb:check-bulanan')
            ->monthlyOn(2, '07:00')
            ->emailOutputOnFailure('admin@instansi.go.id');
        
        // Backup: tanggal 3 jam 07:00 (jika tanggal 2 gagal)
        $schedule->command('kgb:check-bulanan')
            ->monthlyOn(3, '07:00')
            ->emailOutputOnFailure('admin@instansi.go.id');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
