<?php

namespace App\Console;

use App\Jobs\UpdatePermStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    protected function schedule(Schedule $schedule):void {
        $schedule->job(new UpdatePermStatus)->dailyAt('00:00');
        //$schedule->job(new UpdatePermStatus)->everyMinute();
    }
    protected function commands(): void {
        $this->load(__DIR__.'/Commands');
    }

}
