<?php

namespace App\Console;


use App\Jobs\UpdatePermStatus;
use App\Jobs\DelayStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    protected function schedule(Schedule $schedule):void {
        //$schedule->job(new UpdatePermStatus)->dailyAt('00:00');
        $schedule->job(new UpdatePermStatus)->everyMinute();
        $schedule->job(new DelayStatus)->everyMinute();
    }
    protected function commands(): void {
        $this->load(__DIR__.'/Commands');
    }

}
