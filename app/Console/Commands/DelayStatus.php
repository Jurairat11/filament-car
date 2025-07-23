<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Car_report;
use Illuminate\Support\Str;
use App\Models\Car_responses;
use Illuminate\Console\Command;
use Filament\Notifications\Notification;

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
    protected $description = 'Update the status of CAR responses to "delay" if the due date has passed and the status reply is "on process"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $items = Car_responses::whereDate('perm_due_date', '<', $now || 'actual_date', '<', $now)
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

        $item = $items->first(); // Get the first item to use for notification

            $report = Car_report::find($item->car_id);

            if ($report) {
                    // Notify users in dept_id
                    if ($report->dept_id) {
                        $users = User::where('dept_id', $report->dept_id)->get();
                        foreach ($users as $user) {
                            Notification::make()
                                ->title("CAR Overdue")
                                ->icon('heroicon-o-exclamation-triangle')
                                ->iconColor('danger')
                                ->body("CAR no.{$report->car_no} has no replied")
                                ->success()
                                ->sendToDatabase($user);
                        }
                    }

                    // Notify users in responsible_dept_id
                    if ($report->responsible_dept_id) {
                        $users = User::where('dept_id', $report->responsible_dept_id)->get();
                        foreach ($users as $user) {
                            Notification::make()
                                ->title("CAR Overdue")
                                ->icon('heroicon-o-exclamation-triangle')
                                ->iconColor('danger')
                                ->body("CAR no.{$report->car_no} has overdue")
                                ->success()
                                ->sendToDatabase($user);
                        }
            }
        }
            $data = [
                        'car_id' => $item->carReport->car_no ?? '-',
                        'cause' => $item->cause ?? '-',
                        'created_by' => $item->createdResponse->emp_id?? '-',
                    ];

                    $txtTitle = "ใบ CAR เลยกำหนดการตอบกลับ";

                    // create connector instance
                    $connector = new \Sebbmyr\Teams\TeamsConnector(env('MSTEAM_API'));
                    // // create card
                    // $card  = new \Sebbmyr\Teams\Cards\SimpleCard(['title' => $data['title'], 'text' => $data['description']]);

                    // create a custom card
                    $card  = new \Sebbmyr\Teams\Cards\CustomCard("พนักงาน " . Str::upper($data['created_by']), "หัวข้อ: " . $txtTitle);
                    // add information
                    $card->setColor('01BC36')
                        ->addFacts('รายละเอียด', ['เลขที่ CAR ' => $data['car_id'], 'ความไม่ปลอดภัย' => $data['cause']])
                        ->addAction('Visit Issue', route('filament.admin.resources.car-responses.view', $item));
                    // send card via connector
                    $connector->send($card);


    }


}
