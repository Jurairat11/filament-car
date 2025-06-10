<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Car_responses;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdatePermStatus implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    //public $queue = 'perm-status-queue';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $now = Carbon::now();
        $items = Car_responses::whereDate('perm_due_date', '<=', $now)
        ->where('perm_status','=', 'on process')
        ->get();

        foreach($items as $item) {
            $item->perm_status = 'finished';
            $item->save();
        }
    }
}


