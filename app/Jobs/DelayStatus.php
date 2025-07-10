<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Car_report;
use App\Models\Car_responses;
use Illuminate\Support\Str;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DelayStatus implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

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
        //Log::info("Cron is working fine...");

        // Get only records that are not finished (to limit the result set)
        $now = Carbon::now();
        $items = Car_responses::whereDate('perm_due_date', '<=', $now)
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




