<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Car_report;
use Illuminate\Support\Str;
use App\Models\Car_responses;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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

        //แจ้งเตือน
        $pending = Car_report::find($item->car_id);
        if($pending && $pending->dept_id) {
            $user = User::find($pending->dept_id);
        }

        if($user) {
            Notification::make()
            ->title("CAR waiting for review")
            ->icon('heroicon-o-clock')
            ->iconColor('warning')
            ->body("CAR No.{$item->car_no} pending review")
            ->success()
            ->sendToDatabase($user);
        }

        $data = [
                'car_id' => $item->carReport->car_no ?? '-',
                'cause' => $item->cause ?? '-',
                'perm_desc' => $item->perm_desc ?? '-',
                'created_by' => $item->createdResponse->emp_id?? '-',
            ];

            $txtTitle = "ใบ CAR ได้รับการตอบกลับแล้ว";

            // create connector instance
            $connector = new \Sebbmyr\Teams\TeamsConnector(env('MSTEAM_API'));
            // // create card
            // $card  = new \Sebbmyr\Teams\Cards\SimpleCard(['title' => $data['title'], 'text' => $data['description']]);

            // create a custom card
            $card  = new \Sebbmyr\Teams\Cards\CustomCard("พนักงาน " . Str::upper($data['created_by']), "หัวข้อ: " . $txtTitle);
            // add information
            $card->setColor('01BC36')
                ->addFacts('รายละเอียด', ['เลขที่ CAR ' => $data['car_id'], 'ความไม่ปลอดภัย' => $data['cause'],
                'มาตรการการแก้ไขถาวร' => $data['perm_desc']])
                ->addAction('Visit Issue', route('filament.admin.resources.car-responses.view', $item));
            // send card via connector
            $connector->send($card);
    }
}


