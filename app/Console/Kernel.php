<?php

namespace App\Console;

use App\Console\Commands\SendPickupReminders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        SendPickupReminders::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('send:pickup-reminders')
            ->dailyAt(config('reservations.reminder_time', '08:00'));
    }

    protected function commands(): void
    {
        require base_path('routes/console.php');
    }
}
