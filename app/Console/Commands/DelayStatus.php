<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Car_responses;
use Illuminate\Console\Command;

class DelayStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delay-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $items = Car_responses::whereDate('perm_due_date', '<', $now)
        ->where('status_reply', '=', 'on process')
        ->get();

        foreach ($items as $item) {
            //dd($item->days_perm); //-2.0

            // Use the accessor in PHP
            if ($item->days_perm < 0) {
                $item->status_reply = 'delay';
                $item->save();
            }
        }
    }
}
