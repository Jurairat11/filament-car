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

    // หาเฉพาะรายการที่ perm_due_date หรือ actual_date เลยวันนี้ และ status_reply = 'on process'
    $items = Car_responses::where('status_reply', 'on process')
        ->where(function ($query) use ($now) {
            $query->whereDate('perm_due_date', '<', $now)
                ->orWhereDate('actual_date', '<', $now);
        })
        ->get();

    if ($items->isEmpty()) {
        $this->info('No overdue CAR responses found.');
        return;
    }

    foreach ($items as $item) {
        // ตรวจสอบว่า days_perm ติดลบจริง
        if ($item->days_perm < 0) {
            $item->status_reply = 'delay';
            $item->save();

            // ค้นหา CAR Report
            $report = Car_report::find($item->car_id);

            if ($report) {
                // รวมผู้ใช้ที่ต้องแจ้งเตือน
                $usersToNotify = collect();

                if ($report->dept_id) {
                    $usersToNotify = $usersToNotify->merge(
                        User::where('dept_id', $report->dept_id)->get()
                    );
                }

                if ($report->responsible_dept_id) {
                    $usersToNotify = $usersToNotify->merge(
                        User::where('dept_id', $report->responsible_dept_id)->get()
                    );
                }

                // แจ้งเตือนผู้ใช้ทั้งหมดในระบบ
                foreach ($usersToNotify as $user) {
                    Notification::make()
                        ->title("CAR Overdue")
                        ->icon('heroicon-o-exclamation-triangle')
                        ->iconColor('danger')
                        ->body("CAR no. {$report->car_no} has overdue")
                        ->success()
                        ->sendToDatabase($user);
                }
            }

            // ส่งแจ้งเตือน MS Teams
            $data = [
                'car_id' => $item->carReport->car_no ?? '-',
                'cause' => $item->cause ?? '-',
                'created_by' => $item->createdResponse->emp_id ?? '-',
            ];

            $txtTitle = "ใบ CAR เลยกำหนดการตอบกลับ";

            // create connector instance
            $connector = new \Sebbmyr\Teams\TeamsConnector(env('MSTEAM_API'));

            // create a custom card
            $card  = new \Sebbmyr\Teams\Cards\CustomCard(
                "พนักงาน " . Str::upper($data['created_by']),
                "หัวข้อ: " . $txtTitle
            );

            $card->setColor('01BC36')
                ->addFacts('รายละเอียด', [
                    'เลขที่ CAR ' => $data['car_id'],
                    'ความไม่ปลอดภัย' => $data['cause']
                ])
                ->addAction('Visit Issue', route('filament.admin.resources.car-responses.view', $item));

            $connector->send($card);
        }
    }
}



}
