<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Car_responses;
use Illuminate\Console\Command;

class PermStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:perm-status';

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
        $items = Car_responses::whereDate('perm_due_date', '<=', $now)
        ->where('perm_status','=', 'on process')
        ->get();

        foreach($items as $item) {
            $item->perm_status = 'finished';
            $item->save();
        }
    }
}
