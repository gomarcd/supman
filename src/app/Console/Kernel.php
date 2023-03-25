<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\getTickets;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $getTickets = ((new getTickets())->withQuestions());
            Cache::put('getTickets', $getTickets, now()->addMinutes(15));
            Log::channel('schedule')->debug('Scheduled task ran successfully');
        })->everyMinute();
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
