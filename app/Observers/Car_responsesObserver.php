<?php

namespace App\Observers;

use Illuminate\Support\Str;
use App\Models\Car_responses;

class Car_responsesObserver
{
    /**
     * Handle the Car_responses "created" event.
     */
    public function created(Car_responses $car_responses): void
    {

        // $data = [
        //         'car_id' => $car_responses->carReport->car_no ?? '-',
        //         'cause' => $car_responses->cause ?? '-',
        //         'created_by' => $car_responses->createdResponse->emp_id?? '-',
        //     ];

        //     $txtTitle = "ใบ CAR ได้รับการตอบกลับแล้ว";

        //     // create connector instance
        //     $connector = new \Sebbmyr\Teams\TeamsConnector(env('MSTEAM_API'));
        //     // // create card
        //     // $card  = new \Sebbmyr\Teams\Cards\SimpleCard(['title' => $data['title'], 'text' => $data['description']]);

        //     // create a custom card
        //     $card  = new \Sebbmyr\Teams\Cards\CustomCard("พนักงาน " . Str::upper($data['created_by']), "หัวข้อ: " . $txtTitle);
        //     // add information
        //     $card->setColor('01BC36')
        //         ->addFacts('รายละเอียด', ['เลขที่ CAR ' => $data['car_id'], 'สาเหตุ' => $data['cause']])
        //         ->addAction('Visit Issue', route('filament.admin.resources.car-responses.view', $car_responses));
        //     // send card via connector
        //     $connector->send($card);
    }

    /**
     * Handle the Car_responses "updated" event.
     */
    public function updated(Car_responses $car_responses): void
    {
        //
    }

    /**
     * Handle the Car_responses "deleted" event.
     */
    public function deleted(Car_responses $car_responses): void
    {
        //
    }

    /**
     * Handle the Car_responses "restored" event.
     */
    public function restored(Car_responses $car_responses): void
    {
        //
    }

    /**
     * Handle the Car_responses "force deleted" event.
     */
    public function forceDeleted(Car_responses $car_responses): void
    {
        //
    }

    public function saving(Car_responses $car_responses): void
    {

        if (!$car_responses->temp_desc && !$car_responses->temp_status){
            $car_responses->temp_status = 'on process';
        }else{
            $car_responses->temp_status = 'finished';
        }
        // Update perm_status
        // ถ้ามีคำอธิบาย (perm_desc) และยังไม่มีสถานะ (perm_status)
        if ($car_responses->perm_desc && !$car_responses->perm_status) {
            if (now()->lte($car_responses->perm_due_date) || ($car_responses->status_reply = 'delay')) { // วันที่ today (lte less than equal) perm due date
                $car_responses->perm_status = 'on process';
            }
        }

        //Update status_reply
        if (is_null($car_responses->days_perm) && $car_responses->perm_status === 'finished') {
            $car_responses->status_reply = 'finished';

            } elseif ($car_responses->days_perm >= 0) {
                $car_responses->status_reply = 'on process';
            }

        //Update status = pending_review
        if ($car_responses->status_reply === 'finished' && $car_responses->perm_status === 'finished') {
            $car_responses->status = 'pending_review';

            $car_report = $car_responses->carReport; // Access the related model via property

            if($car_report){
                $car_report->status = 'pending_review';
                $car_report->save(); // Save the updated status
            }

        }
    }

}



